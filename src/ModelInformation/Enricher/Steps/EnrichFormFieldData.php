<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeStrategyResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\RelationType;

class EnrichFormFieldData extends AbstractEnricherStep
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
     * @param AttributeStrategyResolver         $attributeStrategyResolver
     * @param RelationStrategyResolver          $relationStrategyResolver
     */
    public function __construct(
        AttributeStrategyResolver $attributeStrategyResolver,
        RelationStrategyResolver $relationStrategyResolver
    ) {
        $this->attributeStrategyResolver = $attributeStrategyResolver;
        $this->relationStrategyResolver  = $relationStrategyResolver;
    }

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
     * Fills form field data if no field data is set.
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

            $fields[ $attribute->name ] = $this->makeModelFormFieldDataForAttributeData($attribute);
        }


        // Add fields for relations
        foreach ($this->info->relations as $relation) {

            $fields[ $relation->name ] = $this->makeModelFormFieldDataForRelationData($relation);
        }


        $this->info->form->fields = $fields;
    }

    /**
     * Enriches existing user configured data.
     *
     * @throws ModelInformationEnrichmentException
     */
    protected function enrichCustomData()
    {
        // Check filled fields and enrich them as required
        // Note that these can be either attributes or relations

        $fields = [];

        foreach ($this->info->form->fields as $key => $field) {

            try {
                $this->enrichField($key, $field, $fields);

            } catch (\Exception $e) {

                // Wrap and decorate exceptions so it is easier to track the problem source
                throw (new ModelInformationEnrichmentException(
                    "Issue with form field '{$key}' (form.fields.{$key}): \n{$e->getMessage()}",
                    $e->getCode(),
                    $e
                ))
                    ->setSection('form.fields')
                    ->setKey($key);
            }
        }

        $this->info->form->fields = $fields;
    }

    /**
     * Enriches a single form field and saves the data.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @param string                                         $key
     * @param array                                          $fields by reference, data array to build, updated with enriched data
     */
    protected function enrichField($key, ModelFormFieldDataInterface $field, array &$fields)
    {
        $normalizedRelationName = $this->normalizeRelationName($key);

        // Check if we can enrich, if we must.
        if (    ! isset($this->info->attributes[ $key ])
            &&  ! isset($this->info->relations[ $normalizedRelationName ])
        ) {
            // Make sure to set the key if it isn't
            if ( ! $field->key()) {
                $field->key = $key;
            }

            $fields[ $key ] = $field;
            return;
        }

        if (isset($this->info->attributes[ $key ])) {
            $enrichFieldInfo = $this->makeModelFormFieldDataForAttributeData($this->info->attributes[ $key ]);
        } else {
            // get from relation data
            $enrichFieldInfo = $this->makeModelFormFieldDataForRelationData(
                $this->info->relations[ $normalizedRelationName ],
                $key
            );
        }

        // Detect whether update/create were not explicitly defined
        // If they were not, assume that they should be shown,
        // since they were explicitly included in the config.
        if (null === $field->update && null === $field->create) {
            $field->update = true;
            $field->create = true;
        }

        $enrichFieldInfo->merge($field);

        $fields[ $key ] = $enrichFieldInfo;
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

        // Orderable column for listify is used in listing, hide in edit form
        if ($info->list->orderable && $info->list->order_column == $attribute->name) {
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

        // Exclude paperclip fields other than the main field
        if (    preg_match('#^(?<field>[^_]+)_(file_name|file_size|content_type|updated_at)$#', $attribute->name, $matches)
            &&  array_has($info->attributes, $matches['field'])
        ) {
            return $info->attributes[ $matches['field'] ]->cast !== AttributeCast::PAPERCLIP_ATTACHMENT;
        }

        // Any attribute that is a foreign key and should be handled with relation-based strategies
        return ! $this->isAttributeForeignKey($attribute->name, $info);
    }

    /**
     * Makes data set for form field given attribute data.
     *
     * @param ModelAttributeData $attribute
     * @return ModelFormFieldData
     */
    protected function makeModelFormFieldDataForAttributeData(ModelAttributeData $attribute)
    {
        $strategy = $this->determineFormDisplayStrategyForAttribute($attribute);

        return new ModelFormFieldData([
            'key'              => $attribute->name,
            'display_strategy' => $strategy,
            'store_strategy'   => $this->determineFormStoreStrategyForAttribute($attribute),
            'translated'       => $attribute->translated,
            'required'         => $this->shouldModelFormFieldBeRequired($attribute, $strategy),
            'options'          => $this->determineFormFieldOptions($attribute),
        ]);
    }

    /**
     * @param ModelAttributeData $attribute
     * @param string             $strategy      the form display strategy alias
     * @return bool
     */
    protected function shouldModelFormFieldBeRequired(ModelAttributeData $attribute, $strategy)
    {
        return  ! in_array($strategy, [ FormDisplayStrategy::BOOLEAN_CHECKBOX ])
            &&  ! $attribute->nullable;
    }

    /**
     * @param ModelAttributeData $attribute
     * @return array
     */
    protected function determineFormFieldOptions(ModelAttributeData $attribute)
    {
        return $this->attributeStrategyResolver->determineFormStoreOptions($attribute);
    }

    /**
     * Makes data set for form field given relation data.
     *
     * @param ModelRelationData $relation
     * @param string|null       $key
     * @return ModelFormFieldData
     */
    protected function makeModelFormFieldDataForRelationData(ModelRelationData $relation, $key = null)
    {
        $required = (   in_array($relation->type, [
                            RelationType::BELONGS_TO,
                            RelationType::BELONGS_TO_THROUGH,
                            RelationType::MORPH_TO,
                        ])
                    &&  ! $relation->nullable_key);

        // Shows any relation, regardless of type. Note that this is not optimal for
        // records with a large number of to-many related items, for instance.

        return new ModelFormFieldData([
            'key'              => $key ?: $relation->name,
            'source'           => $relation->method,
            'required'         => $required,
            'translated'       => $relation->translated,
            'display_strategy' => $this->determineFormDisplayStrategyForRelation($relation),
            'store_strategy'   => $this->determineFormStoreStrategyForRelation($relation),
            'options'          => $this->determineFormStoreOptionsForRelation($relation),
        ]);
    }

    /**
     * @param ModelAttributeData $attribute
     * @return null|string
     */
    protected function determineFormDisplayStrategyForAttribute(ModelAttributeData $attribute)
    {
        return $this->attributeStrategyResolver->determineFormDisplayStrategy($attribute);
    }

    /**
     * @param ModelAttributeData $attribute
     * @return null|string
     */
    protected function determineFormStoreStrategyForAttribute(ModelAttributeData $attribute)
    {
        return $this->attributeStrategyResolver->determineFormStoreStrategy($attribute);
    }

    /**
     * @param ModelRelationData $relation
     * @return null|string
     */
    protected function determineFormDisplayStrategyForRelation(ModelRelationData $relation)
    {
        return $this->relationStrategyResolver->determineFormDisplayStrategy($relation);
    }

    /**
     * @param ModelRelationData $relation
     * @return null|string
     */
    protected function determineFormStoreStrategyForRelation(ModelRelationData $relation)
    {
        return $this->relationStrategyResolver->determineFormStoreStrategy($relation);
    }

    /**
     * @param ModelRelationData $relation
     * @return array
     */
    protected function determineFormStoreOptionsForRelation(ModelRelationData $relation)
    {
        $options = $this->relationStrategyResolver->determineFormStoreOptions($relation);

        // Prepare MorphTo models, if they are not set.
        if ($relation->type === RelationType::MORPH_TO) {
            $options['models'] = $this->determineMorphModelsForRelationData($relation);
        }

        return $options;
    }

    /**
     * Determines models for MorphTo relation data.
     *
     * @param ModelRelationData $data
     * @return string[]
     */
    protected function determineMorphModelsForRelationData(ModelRelationData $data)
    {
        if ($data->morphModels && count($data->morphModels)) {
            return $data->morphModels;
        }

        // Use information for other models in the CMS to find (some of) the related models
        $context = $this->enricher->getAllModelInformation();

        // If there is no context, silently ignore it.
        if ( ! $context) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $models = [];

        foreach ($context as $information) {

            // If a relation is related to this model by a reverse morph relation,
            // it is an intended MorphTo targetable model.
            foreach ($information->relations as $relation) {

                if (    $this->info->modelClass() !== $relation->relatedModel
                    ||  ! in_array($relation->type, [ RelationType::MORPH_ONE, RelationType::MORPH_MANY ])
                ) {
                    continue;
                }

                $models[ $information->modelClass() ] = [];
            }
        }

        return $models;
    }

}
