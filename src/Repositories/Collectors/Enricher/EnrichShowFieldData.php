<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Data\ModelShowFieldDataInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelShowFieldData;
use Czim\CmsModels\Support\Data\ModelRelationData;
use UnexpectedValueException;

class EnrichShowFieldData extends EnrichListColumnData
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->show->fields)) {
            $this->fillDataForEmpty();
        } else {
            $this->enrichCustomData();
        }
    }

    /**
     * Fills field data if no custom data is set.
     */
    protected function fillDataForEmpty()
    {
        // Fill list references if they are empty
        $fields = [];

        // Add fields for attributes
        foreach ($this->info->attributes as $attribute) {

            if ($attribute->hidden || ! $this->shouldAttributeBeDisplayedByDefault($attribute, $this->info)) {
                continue;
            }

            $fields[ $attribute->name ] = $this->makeModelShowFieldDataForAttributeData($attribute, $this->info);
        }

        // Add fields for relations?
        // Perhaps, though it may be fine to leave this up to manual configuration.
        // todo: consider


        $this->info->show->fields = $fields;
    }

    /**
     * Enriches existing user configured data.
     */
    protected function enrichCustomData()
    {
        // Check filled fields and enrich them as required
        // Note that these can be either attributes or relations

        $fields = [];

        foreach ($this->info->show->fields as $key => $field) {

            $normalizedRelationKey = $this->normalizeRelationKey($key);

            // Check if we can enrich, if we must.
            if (    ! isset($this->info->attributes[ $key ])
                &&  ! isset($this->info->relations[ $normalizedRelationKey ])
                &&  ! isset($this->info->relations[ $key ])
            ) {
                // if the field data is fully set, no need to enrich
                if ($this->isShowFieldDataComplete($field)) {
                    $fields[ $key ] = $field;
                    continue;
                }

                throw new UnexpectedValueException(
                    "Unenriched show field set with non-attribute/non-relation-name key; "
                    . "make sure full field data is provided"
                );
            }

            if (isset($this->info->attributes[ $key ])) {
                $attributeFieldInfo = $this->makeModelShowFieldDataForAttributeData($this->info->attributes[ $key ], $this->info);
            } else {
                // get from relation data

                $relationData = array_key_exists($normalizedRelationKey, $this->info->relations)
                    ?   $this->info->relations[ $normalizedRelationKey ]
                    :   $this->info->relations[ $key ];

                $attributeFieldInfo = $this->makeModelShowFieldDataForRelationData($relationData, $this->info);
            }

            $attributeFieldInfo->merge($field);

            $fields[ $key ] = $attributeFieldInfo;
        }

        $this->info->show->fields = $fields;
    }

    /**
     * Returns whether the given data set is filled to the extent that enrichment is not required.
     *
     * @param ModelShowFieldDataInterface|ModelShowFieldData $data
     * @return bool
     */
    protected function isShowFieldDataComplete(ModelShowFieldDataInterface $data)
    {
        return $data->source && $data->strategy;
    }

    /**
     * Makes data set for show field given attribute data.
     *
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelShowFieldData
     */
    protected function makeModelShowFieldDataForAttributeData(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        $primaryIncrementing = $attribute->name === $this->model->getKeyName() && $info->incrementing;

        return new ModelShowFieldData([
            'source'         => $attribute->name,
            'strategy'       => $this->determineListDisplayStrategyForAttribute($attribute),
            'style'          => $primaryIncrementing ? 'primary-id' : null,
            'label'          => ucfirst(str_replace('_', ' ', snake_case($attribute->name))),
        ]);
    }

    /**
     * Makes data set for list field given relation data.
     *
     * @param ModelRelationData                          $relation
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelShowFieldData
     */
    protected function makeModelShowFieldDataForRelationData(ModelRelationData $relation, ModelInformationInterface $info)
    {
        return new ModelShowFieldData([
            'source'         => $relation->method,
            'strategy'       => $this->determineListDisplayStrategyForRelation($relation),
            'label'          => ucfirst(str_replace('_', ' ', snake_case($relation->method))),
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

    /**
     * @param ModelRelationData $relation
     * @return null|string
     */
    protected function determineListDisplayStrategyForRelation(ModelRelationData $relation)
    {
        return $this->relationStrategyResolver->determineListDisplayStrategy($relation);
    }

}
