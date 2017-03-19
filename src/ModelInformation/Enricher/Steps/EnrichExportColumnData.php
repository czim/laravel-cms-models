<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeStrategyResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Export\ModelExportColumnDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportColumnData;
use Czim\CmsModels\Support\Enums\RelationType;
use UnexpectedValueException;

class EnrichExportColumnData extends AbstractEnricherStep
{
    /**
     * @var AttributeStrategyResolver
     */
    protected $attributeStrategyResolver;

    /**
     * @var RelationStrategyResolver
     */
    protected $relationStrategyResolver;

    /**
     * @param ModelInformationEnricherInterface $enricher
     * @param AttributeStrategyResolver         $attributeStrategyResolver
     * @param RelationStrategyResolver          $relationStrategyResolver
     */
    public function __construct(
        ModelInformationEnricherInterface $enricher,
        AttributeStrategyResolver $attributeStrategyResolver,
        RelationStrategyResolver $relationStrategyResolver
    ) {
        parent::__construct($enricher);

        $this->attributeStrategyResolver = $attributeStrategyResolver;
        $this->relationStrategyResolver  = $relationStrategyResolver;
    }

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->export->columns)) {
            $this->fillDataForEmpty();
        } else {
            $this->enrichCustomData();
        }

        // Separate strategies may have their own column setups.
        // If not, the defaults should be copied for them.
        foreach ($this->info->export->strategies as $key => $strategyData) {

            if ( ! count($strategyData->columns)) {
                $this->info->export->strategies[ $key ]->columns = $this->info->export->columns;
            } else {
                $this->enrichCustomData($key);
            }
        }
    }

    /**
     * Fills column data if no custom data is set.
     */
    protected function fillDataForEmpty()
    {
        // Fill export columns if they are empty
        $columns = [];

        $foreignKeys = $this->collectForeignKeys();

        // Add columns for attributes
        foreach ($this->info->attributes as $attribute) {

            if ($attribute->hidden || in_array($attribute->name, $foreignKeys)) {
                continue;
            }

            $columns[ $attribute->name ] = $this->makeModelExportColumnDataForAttributeData($attribute, $this->info);
        }

        $this->info->export->columns = $columns;
    }

    /**
     * Returns list of foreign key attribute names on this model.
     *
     * @return string[]
     */
    protected function collectForeignKeys()
    {
        $keys = [];

        foreach ($this->info->relations as $relation) {

            if ( ! in_array(
                $relation->type,
                [ RelationType::BELONGS_TO, RelationType::MORPH_TO, RelationType::BELONGS_TO_THROUGH ]
            )) {
                continue;
            }

            $keys = array_merge($keys, $relation->foreign_keys);
        }

        return $keys;
    }

    /**
     * Enriches existing user configured data.
     *
     * @param string|null $strategy
     * @throws ModelInformationEnrichmentException
     */
    protected function enrichCustomData($strategy = null)
    {
        // Check filled columns and enrich them as required
        // Note that these can be either attributes or relations

        $columns = [];

        if (null === $strategy) {
            $columnsOrigin = $this->info->export->columns;
        } else {
            $columnsOrigin = $this->info->export->strategies[ $strategy ]->columns;
        }

        foreach ($columnsOrigin as $key => $column) {

            try {
                $this->enrichColumn($key, $column, $columns);

            } catch (\Exception $e) {

                $section = 'export' . ($strategy ? ".{$strategy}" : null) . '.columns';

                $decoratedMessage = "Issue with export column '{$key}' "
                                  . ($strategy ? " for strategy '{$strategy}' " : null)
                                  . "({$section}.{$key}): \n{$e->getMessage()}";

                // Wrap and decorate exceptions so it is easier to track the problem source
                throw (new ModelInformationEnrichmentException($decoratedMessage, $e->getCode(), $e))
                    ->setSection($section)
                    ->setKey($key);
            }
        }


        if (null === $strategy) {
            $this->info->export->columns = $columns;
        } else {
            $this->info->export->strategies[ $strategy ]->columns = $columns;
        }
    }

    /**
     * Enriches a single export column and saves the data.
     *
     * @param ModelExportColumnDataInterface $column
     * @param string                       $key
     * @param array                        $columns     by reference, data array to build, updated with enriched data
     */
    protected function enrichColumn($key, ModelExportColumnDataInterface $column, array &$columns)
    {
        // Check if we can enrich, if we must.
        if ( ! isset($this->info->attributes[ $key ])) {

            // if the column data is fully set, no need to enrich
            if ($this->isExportColumnDataComplete($column)) {
                $columns[ $key ] = $column;
                return;
            }

            throw new UnexpectedValueException(
                "Incomplete data for for export column key that does not match known model attribute or relation method. "
                . "Requires at least 'source' value."
            );
        }


        $attributeColumnInfo = $this->makeModelExportColumnDataForAttributeData($this->info->attributes[ $key ], $this->info);

        $attributeColumnInfo->merge($column);

        $columns[ $key ] = $attributeColumnInfo;
    }

    /**
     * Returns whether the given data set is filled to the extent that enrichment is not required.
     *
     * @param ModelExportColumnDataInterface|ModelExportColumnData $data
     * @return bool
     */
    protected function isExportColumnDataComplete(ModelExportColumnDataInterface $data)
    {
        return (bool) $data->source;
    }

    /**
     * Makes data set for export column given attribute data.
     *
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelExportColumnData
     */
    protected function makeModelExportColumnDataForAttributeData(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        return new ModelExportColumnData([
            'hide'     => false,
            'source'   => $attribute->name,
            'strategy' => $this->determineExportColumnStrategyForAttribute($attribute),
        ]);
    }

    /**
     * @param ModelAttributeData $attribute
     * @return null|string
     */
    protected function determineExportColumnStrategyForAttribute(ModelAttributeData $attribute)
    {
        return $this->attributeStrategyResolver->determineExportColumnStrategy($attribute);
    }

}
