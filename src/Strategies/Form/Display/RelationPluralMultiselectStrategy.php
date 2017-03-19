<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class RelationPluralMultiselectStrategy
 *
 * Not advisable for large datasets.
 */
class RelationPluralMultiselectStrategy extends AbstractRelationStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.relation_plural_multiselect';
    }

    /**
     * Normalizes a value to make sure it can be processed uniformly.
     *
     * @param mixed $value
     * @param bool  $original
     * @return mixed
     */
    protected function normalizeValue($value, $original = false)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
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
        // Get the key-reference pairs required to fill the drop-down

        $referenceData = $this->getReferenceDataProvider()->getForModelClassByType(
            get_class($this->model),
            'form.field',
            $this->field->key()
        );

        if ($referenceData) {
            $references = $this->getReferenceRepository()->getReferencesForModelMetaReference($referenceData);
        } else {
            $references = [];
        }

        $data['dropdownOptions'] = $references;

        return $data;
    }

}
