<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\FilterStrategy;
use Czim\CmsModels\Support\Enums\RelationType;
use UnexpectedValueException;

class EnrichListFilterData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->list->filters)) {
            $this->fillDataForEmpty();
        } else {
            $this->enrichCustomData();
        }
    }

    /**
     * Fills filter data if no custom data is set.
     */
    protected function fillDataForEmpty()
    {
        // We either create separate filters, or combine strings in a single 'any' field
        if (config('cms-models.analyzer.filters.single-any-string')) {
            $filters = $this->makeCombinedFiltersWithAnyStringFilter();
        } else {
            $filters = $this->makeFiltersForAllAttributes();
        }

        $this->info->list->filters = $filters;
    }

    /**
     * Makes a single 'any' string filter and optionally complementary filter data sets.
     *
     * @return ModelListFilterData[]
     */
    protected function makeCombinedFiltersWithAnyStringFilter()
    {
        $filters = [];

        $anyKey = config('cms-models.analyzer.any-string-key', 'any');
        $filters[ $anyKey ] = $this->makeModelListFilterDataForAnyStringFilter();

        return $filters;
    }

    /**
     * Makes separate filter data sets for all attributes.
     *
     * @return ModelListFilterData[]
     */
    protected function makeFiltersForAllAttributes()
    {
        $filters = [];

        foreach ($this->info->attributes as $attribute) {

            if ($attribute->hidden || ! $this->shouldAttributeBeFilterable($attribute)) {
                continue;
            }

            $filterData = $this->makeModelListFilterDataForAttributeData($attribute, $this->info);

            if ( ! $filterData) continue;

            $filters[$attribute->name] = $filterData;
        }

        return $filters;
    }

    /**
     * Enriches existing user configured data.
     */
    protected function enrichCustomData()
    {
        // Check set filters and enrich them as required
        $filters = [];

        foreach ($this->info->list->filters as $key => $filter) {

            try {
                $this->enrichFilter($key, $filter, $filters);

            } catch (\Exception $e) {

                // Wrap and decorate exceptions so it is easier to track the problem source
                throw (new ModelInformationEnrichmentException(
                    "Issue with list filter '{$key}' (list.filters.{$key}): \n{$e->getMessage()}",
                    $e->getCode(),
                    $e
                ))
                    ->setSection('list.filters')
                    ->setKey($key);
            }
        }

        $this->info->list->filters = $filters;
    }

    /**
     * Enriches a single list column and saves the data.
     *
     * @param ModelFilterDataInterface $filter
     * @param string                   $key
     * @param array                    $filters     by reference, data array to build, updated with enriched data
     */
    protected function enrichFilter($key, ModelFilterDataInterface $filter, array &$filters)
    {
        // If the filter information is fully provided, do not try to enrich
        if ($this->isListFilterDataComplete($filter)) {
            $filters[ $key ] = $filter;
            return;
        }

        if ( ! isset($this->info->attributes[ $key ])) {

            throw new UnexpectedValueException(
                "Incomplete data for for list filter key that does not match known model attribute or relation method. "
                . "Requires at least 'strategy' and 'target' values."
            );
        }

        $attributeFilterInfo = $this->makeModelListFilterDataForAttributeData($this->info->attributes[ $key ], $this->info);

        if (false === $attributeFilterInfo) {

            throw new UnexpectedValueException(
                "Uninterpretable data for for list filter key, cannot determine defaults based on model attribute or relation method name. "
                . "Requires at least 'strategy' and 'target' values."
            );
        }

        $attributeFilterInfo->merge($filter);

        $filters[ $key ] = $attributeFilterInfo;
    }

    /**
     * Returns whether a given attribute should be filterable by default.
     *
     * @param ModelAttributeDataInterface|ModelAttributeData $attribute
     * @return bool
     */
    protected function shouldAttributeBeFilterable(ModelAttributeDataInterface $attribute)
    {
        // If an attribute is a foreign key, it shouldn't be a filter by default.

        foreach ($this->info->relations as $key => $relation) {

            switch ($relation->type) {

                case RelationType::MORPH_TO:
                case RelationType::BELONGS_TO:
                case RelationType::BELONGS_TO_THROUGH:

                    $keys = $relation->foreign_keys ?: [];

                    if (in_array($attribute->name, $keys)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Returns whether the given data set is filled to the extent that enrichment is not required.
     *
     * @param ModelFilterDataInterface|ModelListFilterData $data
     * @return bool
     */
    protected function isListFilterDataComplete(ModelFilterDataInterface $data)
    {
        return $data->strategy && $data->target;
    }

    /**
     * Makes list filter data given attribute data.
     *
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelListFilterData|false
     */
    protected function makeModelListFilterDataForAttributeData(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        $strategy = false;
        $options  = [];

        if ($attribute->cast === AttributeCast::BOOLEAN) {

            $strategy = FilterStrategy::BOOLEAN;

        } elseif ($attribute->type === 'enum') {

            $strategy = FilterStrategy::DROPDOWN;
            $options['values'] = $attribute->values;

        } elseif ($attribute->cast === AttributeCast::STRING) {

            $strategy = FilterStrategy::STRING;

        } elseif ($attribute->cast === AttributeCast::DATE) {

            $strategy = FilterStrategy::DATE;
        }

        if ( ! $strategy) {
            return false;
        }

        return new ModelListFilterData([
            'source'   => $attribute->name,
            'label'    => str_replace('_', ' ', snake_case($attribute->name)),
            'target'   => $attribute->name,
            'strategy' => $strategy,
            'options'  => $options,
        ]);
    }

    /**
     * Makes list filter data given attribute data.
     *
     * @return ModelListFilterData
     */
    protected function makeModelListFilterDataForAnyStringFilter()
    {
        return new ModelListFilterData([
            'label_translated' => $this->getAnyStringFilterLabel(),
            'target'           => '*',
            'strategy'         => 'string-split',
        ]);
    }

    /**
     * Returns translation label for any string filter.
     *
     * @return string
     */
    protected function getAnyStringFilterLabel()
    {
        return 'models.filter.anything-label';
    }

}
