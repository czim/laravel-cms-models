<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Analyzer\AttributeStrategyResolver;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Data\ModelListColumnDataInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListColumnData;
use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use UnexpectedValueException;

class EnrichListColumnData extends AbstractEnricherStep
{
    /**
     * @var AttributeStrategyResolver
     */
    protected $attributeStrategyResolver;

    /**
     * @param AttributeStrategyResolver $attributeStrategyResolver
     */
    public function __construct(AttributeStrategyResolver $attributeStrategyResolver)
    {
        $this->attributeStrategyResolver = $attributeStrategyResolver;
    }

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->list->columns)) {
            $this->fillDataForEmpty();
        } else {
            $this->enrichCustomData();
        }
    }

    /**
     * Fills column data if no custom data is set.
     */
    protected function fillDataForEmpty()
    {
        // Fill list references if they are empty
        $columns = [];

        // Add columns for attributes
        foreach ($this->info->attributes as $attribute) {

            if ($attribute->hidden || ! $this->shouldAttributeBeDisplayedByDefault($attribute, $this->info)) {
                continue;
            }

            $columns[ $attribute->name ] = $this->makeModelListColumnDataForAttributeData($attribute, $this->info);
        }

        // Add columns for relations?
        // Perhaps, though it may be fine to leave this up to manual configuration.
        // todo: consider


        $this->info->list->columns = $columns;
    }

    /**
     * Enriches existing user configured data.
     */
    protected function enrichCustomData()
    {
        // Check filled columns and enrich them as required
        // Note that these can be either attributes or relations

        $columns = [];

        foreach ($this->info->list->columns as $key => $column) {

            // Check if we can enrich, if we must.
            if ( ! isset($this->info->attributes[ $key ]) && ! isset($this->info->relations[ $key ])) {

                // if the column data is fully set, no need to enrich
                if ($this->isListColumnDataComplete($column)) {
                    $columns[ $key ] = $column;
                    continue;
                }

                throw new UnexpectedValueException(
                    "Unenriched list column set with non-attribute/non-relation-name key; "
                    . "make sure full column data is provided"
                );
            }

            if (isset($this->info->attributes[ $key ])) {
                $attributeColumnInfo = $this->makeModelListColumnDataForAttributeData($this->info->attributes[ $key ], $this->info);
            } else {
                // get from relation data
                $attributeColumnInfo = $this->makeModelListColumnDataForRelationData($this->info->relations[ $key ], $this->info);
            }

            $attributeColumnInfo->merge($column);

            $columns[ $key ] = $attributeColumnInfo;
        }

        $this->info->list->columns = $columns;
    }

    /**
     * Returns whether the given data set is filled to the extent that enrichment is not required.
     *
     * @param ModelListColumnDataInterface|ModelListColumnData $data
     * @return bool
     */
    protected function isListColumnDataComplete(ModelListColumnDataInterface $data)
    {
        return $data->source && $data->strategy;
    }

    /**
     * Makes data set for list column given attribute data.
     *
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelListColumnData
     */
    protected function makeModelListColumnDataForAttributeData(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        $primaryIncrementing = $attribute->name === $this->model->getKeyName() && $info->incrementing;

        $sortable = (
            $attribute->isNumeric()
            ||  in_array($attribute->cast, [
                AttributeCast::BOOLEAN,
                AttributeCast::DATE,
                AttributeCast::STRING,
            ])
            &&  ! in_array($attribute->type, [
                'text', 'longtext', 'mediumtext',
                'blob', 'longblob', 'mediumblob',
            ])
        );

        $sortDirection = 'asc';
        if (    $primaryIncrementing
            ||  in_array($attribute->cast, [ AttributeCast::BOOLEAN, AttributeCast::DATE ])
        ) {
            $sortDirection = 'desc';
        }

        return new ModelListColumnData([
            'source'         => $attribute->name,
            'strategy'       => $this->determineListDisplayStrategyForAttribute($attribute),
            'style'          => $primaryIncrementing ? 'primary-id' : null,
            'editable'       => $attribute->fillable,
            'sortable'       => $sortable,
            'sort_strategy'  => $attribute->translated ? 'translated' : null,
            'sort_direction' => $sortDirection,
        ]);
    }

    /**
     * Makes data set for list column given relation data.
     *
     * @param ModelRelationData                          $relation
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelListColumnData
     */
    protected function makeModelListColumnDataForRelationData(ModelRelationData $relation, ModelInformationInterface $info)
    {
        return new ModelListColumnData([
            'source'         => $relation->method,
            'strategy'       => $relation->strategy_list ?: $relation->strategy,
            'label'          => ucfirst(str_replace('_', ' ', snake_case($relation->method))),
            'sortable'       => false,
        ]);
    }

    /**
     * @param ModelAttributeData $attribute
     * @return null|string
     */
    protected function determineListDisplayStrategyForAttribute(ModelAttributeData $attribute)
    {
        return $this->attributeStrategyResolver->determineListDisplayStrategy($attribute);
    }

}
