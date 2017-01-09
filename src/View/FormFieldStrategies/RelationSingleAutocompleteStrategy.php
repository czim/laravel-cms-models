<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class RelationSingleAutocompleteStrategy extends AbstractRelationStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.relation_single_autocomplete';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        // Get the key-reference pairs to allow the form to display values for the
        // currently selected keys for the model.

        $data['references'] = $this->getReferencesForModelKeys([ $data['value'] ]);

        // Determine the min. input length to trigger autocomplete ajax lookups
        $data['minimumInputLength'] = array_get(
            $this->field->options(),
            'minimum_input_length',
            $this->determineBestMinimumInputLength()
        );

        return $data;
    }

}
