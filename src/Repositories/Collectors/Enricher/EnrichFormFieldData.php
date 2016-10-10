<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\RelationType;
use UnexpectedValueException;

class EnrichFormFieldData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->form->fields)) {
            $this->fillDataForEmpty();
        } else {
            $this->enrichCustomData();
        }
    }

    /**
     * Fills column data if no field data is set.
     */
    protected function fillDataForEmpty()
    {
        // Fill field references if they are empty
        $fields = [];

        // Add columns for attributes
        foreach ($this->info->attributes as $attribute) {

            if ($attribute->hidden || ! $this->shouldAttributeBeEditableByDefault($attribute, $this->info)) {
                continue;
            }

            $fields[ $attribute->name ] = $this->makeModelFormFieldDataForAttributeData($attribute, $this->info);
        }


        // Add fields for relations
        // todo: make this, consider which to include


        $this->info->form->fields = $fields;
    }

    /**
     * Enriches existing user configured data.
     */
    protected function enrichCustomData()
    {
        // Check filled fields and enrich them as required
        // Note that these can be either attributes or relations

        $fields = [];

        foreach ($this->info->form->fields as $key => $field) {

            // Check if we can enrich, if we must.
            if ( ! isset($this->info->attributes[ $key ]) && ! isset($this->info->relations[ $key ])) {

                // if the data is fully set, no need to enrich
                if ($this->isFormFieldDataComplete($field)) {
                    $fields[ $key ] = $field;
                    continue;
                }

                throw new UnexpectedValueException(
                    "Unenriched form field set with non-attribute/non-relation-name key; "
                    . "make sure full field data is provided"
                );
            }

            if (isset($this->info->attributes[ $key ])) {
                $attributeFieldInfo = $this->makeModelFormFieldDataForAttributeData($this->info->attributes[ $key ], $this->info);
            } else {
                // get from relation data
                $attributeFieldInfo = $this->makeModelFormFieldDataForRelationData($this->info->relations[ $key ], $this->info);
            }


            $attributeFieldInfo->merge($field);

            $fields[ $key ] = $attributeFieldInfo;
        }

        $this->info->form->fields = $fields;
    }

    /**
     * Returns whether the given data set is filled to the extent that enrichment is not required.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $data
     * @return bool
     */
    protected function isFormFieldDataComplete(ModelFormFieldDataInterface $data)
    {
        return $data->key;
    }

    /**
     * Returns whether an attribute should be editable if no user-defined fields are configured.
     *
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return bool
     */
    protected function shouldAttributeBeEditableByDefault(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        // Auto-incrementing key
        if ($attribute->name === $this->model->getKeyName() && $info->incrementing) {
            return false;
        }

        // Activatable column is used in listing, so hide in edit form
        if ($info->list->activatable && $info->list->active_column == $attribute->name) {
            return false;
        }

        // Automated timestamp columns
        if (    $this->model->timestamps
            &&  (   $attribute->name == $this->model->getCreatedAtColumn()
                ||  $attribute->name == $this->model->getUpdatedAtColumn()
                )
        ) {
            return false;
        }

        // Exclude stapler fields other than the main field
        if (preg_match('#^(?<field>[^_]+)_(file_name|file_size|content_type|updated_at)$#', $attribute->name, $matches)) {
            if (array_has($info->attributes, $matches['field'])) {
                $strategy = $info->attributes[ $matches['field'] ]->strategy_form ?: $info->attributes[ $matches['field'] ]->strategy;
                return ! in_array($strategy, $this->getStaplerStrategies());
            }
        }

        return true;
    }

    /**
     * Makes data set for form field given attribute data.
     *
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelFormFieldData
     */
    protected function makeModelFormFieldDataForAttributeData(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        return new ModelFormFieldData([
            'key'              => $attribute->name,
            'display_strategy' => $attribute->strategy_form ?: $attribute->strategy,
            'translated'       => $attribute->translated,
            'required'         => ! $attribute->nullable,
        ]);
    }

    /**
     * Makes data set for form field given relation data.
     *
     * @param ModelRelationData                          $relation
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelFormFieldData
     */
    protected function makeModelFormFieldDataForRelationData(ModelRelationData $relation, ModelInformationInterface $info)
    {
        $required = (   (   $relation->type == RelationType::BELONGS_TO
                        ||  $relation->type == RelationType::BELONGS_TO_THROUGH)
                    &&  ! $relation->nullable_key);

        return new ModelFormFieldData([
            'key'              => $relation->method,
            'display_strategy' => $relation->strategy_form ?: $relation->strategy,
            'required'         => $required,
        ]);
    }

}
