<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

use Illuminate\Contracts\Support\Arrayable;

class RelationPluralAutocompleteStrategy extends AbstractRelationStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.relation_plural_autocomplete';
    }

    /**
     * Normalizes a value to make sure it can be processed uniformly.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function normalizeValue($value)
    {
        if ($value instanceof Arrayable || is_array($value)) {
            return $value;
        }

        return [ $value ];
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

        $keys = $data['value'] ?: [];

        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }

        $data['references'] = $this->getReferencesForModelKeys($keys);

        return $data;
    }

}
