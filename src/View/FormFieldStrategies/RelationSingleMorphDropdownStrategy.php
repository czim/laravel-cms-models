<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

use Czim\CmsModels\Support\Strategies\Traits\HasMorphRelationStrategyOptions;

/**
 * Class RelationSingleMorphDropdownStrategy
 *
 * Normal select dropdown for polymorphic (MorphTo) relation references.
 * Not advisable to use with large resultsets.
 */
class RelationSingleMorphDropdownStrategy extends AbstractRelationStrategy
{
    // The separator symbol that splits the model class and model key parts of the value.
    const CLASS_AND_KEY_SEPARATOR = ':';

    use HasMorphRelationStrategyOptions;


    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.relation_single_morph_dropdown';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        // Get the key-reference pairs required to fill the drop-down and group them by the model class.
        // Also get displayable labels for each model class.

        $modelClasses = $this->getMorphableModels();

        $modelLabels = [];
        $references  = [];

        foreach ($modelClasses as $modelClass) {

            $modelLabels[ $modelClass ] = $this->getModelDisplayLabel($modelClass);

            $referenceData = $this->getReferenceDataProvider()->getForModelClassByType(
                get_class($this->model),
                'form.field',
                $this->field->key(),
                $modelClass
            );

            $references[$modelClass] = [];

            if ($referenceData) {
                foreach (
                    $this->getReferenceRepository()->getReferencesForModelMetaReference($referenceData)
                    as $referenceKey => $reference
                ) {
                    $references[$modelClass][ $modelClass . static::CLASS_AND_KEY_SEPARATOR . $referenceKey] = $reference;
                }
            }
        }

        $data['modelLabels']     = $modelLabels;
        $data['dropdownOptions'] = $references;

        return $data;
    }

    /**
     * Returns the model class names that the model may be related to.
     *
     * @return string[]
     */
    protected function getMorphableModels()
    {
        return $this->getMorphableModelsForFieldData($this->field);
    }

}
