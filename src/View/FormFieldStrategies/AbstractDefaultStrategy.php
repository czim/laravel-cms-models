<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

abstract class AbstractDefaultStrategy extends AbstractFormFieldDisplayStrategy
{

    /**
     * Renders a form field.
     *
     * @param mixed       $value
     * @param mixed       $originalValue
     * @param array       $errors
     * @param null|string $locale
     * @return string
     */
    public function renderField($value, $originalValue, array $errors = [], $locale = null)
    {
        $value         = $this->normalizeValue($value);
        $originalValue = $this->normalizeValue($originalValue, true);

        $type = $this->field->type ?: array_get($this->field->options(), 'type', 'text');

        $data = [
            'record'     => $this->model,
            'key'        => $this->field->key() . ($locale ? '-locale:' . $locale : null),
            'baseKey'    => $this->field->key(),
            'name'       => $this->getFormFieldName($locale),
            'value'      => $value,
            'original'   => $originalValue,
            'type'       => $type,
            'errors'     => $errors,
            'required'   => $this->field->required(),
            'options'    => $this->field->options(),
            'translated' => $this->field->translated(),
        ];

        return view($this->getView(), $this->decorateFieldData($data));
    }

    /**
     * Normalizes a value to make sure it can be processed uniformly.
     *
     * @param mixed $value
     * @param bool  $original   whether the value is the original value for the persisted model
     * @return mixed
     */
    protected function normalizeValue($value, $original = false)
    {
        return $value;
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        return $data;
    }

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    abstract protected function getView();

    /**
     * Returns name for the form field input tag.
     *
     * @param null|string $locale
     * @return string
     */
    protected function getFormFieldName($locale = null)
    {
        if ( ! $locale) {
            return $this->field->key();
        }

        return $this->field->key() . '[' . $locale . ']';
    }

}
