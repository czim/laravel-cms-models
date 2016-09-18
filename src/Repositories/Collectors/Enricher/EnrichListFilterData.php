<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Support\Enums\AttributeCast;
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
        // Set default filters if they are empty
        $filters = [];

        foreach ($this->info->attributes as $attribute) {

            if ($attribute->hidden) continue;

            $filterData = $this->makeModelListFilterDataForAttributeData($attribute, $this->info);

            if ( ! $filterData) continue;

            $filters[$attribute->name] = $filterData;
        }

        $this->info->list->filters = $filters;
    }

    /**
     * Enriches existing user configured data.
     */
    protected function enrichCustomData()
    {
        // Check set filters and enrich them as required
        $filters = [];

        foreach ($this->info->list->filters as $key => $filter) {

            // If the filter information is fully provided, do not try to enrich
            if ($this->isListFilterDataComplete($filter)) {
                $filters[ $key ] = $filter;
                continue;
            }

            if ( ! isset($this->info->attributes[ $key ])) {
                throw new UnexpectedValueException(
                    "Unenriched list filter set with non-attribute key; make sure full filter data is provided ({$key})"
                );
            }

            $attributeFilterInfo = $this->makeModelListFilterDataForAttributeData($this->info->attributes[ $key ], $this->info);

            if (false === $attributeFilterInfo) {
                throw new UnexpectedValueException(
                    "Unenriched list filter set for uninterpretable attribute for filter data;"
                    . " make sure full filter data is provided ({$key})"
                );
            }

            $attributeFilterInfo->merge($filter);

            $filters[ $key ] = $attributeFilterInfo;
        }

        $this->info->list->filters = $filters;
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

            $strategy = 'boolean';

        } elseif ($attribute->type === 'enum') {

            $strategy = 'enum';
            $options  = $attribute->values;

        } elseif ($attribute->cast === AttributeCast::STRING) {

            $strategy = 'string';
        }

        if ( ! $strategy) {
            return false;
        }

        return new ModelListFilterData([
            'source'   => $attribute->name,
            'label'    => str_replace('_', ' ', snake_case($attribute->name)),
            'target'   => $attribute->name,
            'strategy' => $strategy,
            'values'   => $options,
        ]);
    }

}
