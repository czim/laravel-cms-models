<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

use Czim\CmsModels\Support\Strategies\Traits\HasMorphRelationStrategyOptions;
use UnexpectedValueException;

class RelationSingleMorphAutocompleteStrategy extends AbstractRelationStrategy
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
        return 'cms-models::model.partials.form.strategies.relation_single_morph_autocomplete';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        // Get displayable labels for each model class.

        $modelClasses = $this->getMorphableModels();
        $modelLabels  = [];

        foreach ($modelClasses as $modelClass) {
            $modelLabels[ $modelClass ] = $this->getModelDisplayLabel($modelClass);
        }

        $data['modelLabels'] = $modelLabels;


        // Get the key-reference pairs required to fill the pre-selected option.
        $valueClass = $this->getModelClassFromValue($data['value']);
        $valueKey   = $this->getModelKeyFromValue($data['value']);

        if ($valueClass && $valueKey) {
            $data['references'] = $this->getReferencesForModelKeys([ $valueKey ], $valueClass);
        } else {
            $data['references'] = [];
        }


        // Determine the min. input length to trigger autocomplete ajax lookups
        // Since it may be assumed that the result count is large for combined model results,
        // always default to at least 1 character.
        $data['minimumInputLength'] = array_get(
            $this->field->options(),
            'minimum_input_length',
            $this->determineBestMinimumInputLength()
        );

        return $data;
    }

    /**
     * Returns references for model keys as an array keyed per model key.
     *
     * @param mixed[]     $keys
     * @param string|null $targetModel  the nested model class, if multiple model definitions set
     * @return string[] associative
     */
    protected function getReferencesForModelKeys(array $keys, $targetModel = null)
    {
        $references = parent::getReferencesForModelKeys($keys, $targetModel);

        $normalizedReferences = [];

        foreach ($references as $key => $reference) {
            $normalizedReferences[ $targetModel . static::CLASS_AND_KEY_SEPARATOR . $key ] = $reference;
        }

        return $normalizedReferences;
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

    /**
     * Returns the model class part of a morph model/key combination value.
     *
     * @param string $value
     * @return string|null
     */
    protected function getModelClassFromValue($value)
    {
        if (empty($value)) return null;

        $parts = explode(static::CLASS_AND_KEY_SEPARATOR, $value, 2);

        return $parts[0];
    }

    /**
     * Returns the model key part of a morph model/key combination value.
     *
     * @param string $value
     * @return mixed|null
     */
    protected function getModelKeyFromValue($value)
    {
        if (empty($value)) return null;

        $parts = explode(static::CLASS_AND_KEY_SEPARATOR, $value, 2);

        if (count($parts) < 2) {
            throw new UnexpectedValueException("Morph model value is not formatted as 'class:key'.");
        }

        return $parts[1];
    }

}
