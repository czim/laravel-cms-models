<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsModels\Exceptions\FormFieldDisplayException;

class DefaultStrategy extends AbstractDefaultStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.default';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     * @throws FormFieldDisplayException
     */
    protected function decorateFieldData(array $data)
    {
        // Prevent causing errors in the view if the value is not castable to string.
        try {
            $data['value'] = (string) $data['value'];

        } catch (\Exception $e) {

            throw new FormFieldDisplayException(
                "Failed to cast value to string for field '{$this->field->key()}' "
                . "with source '{$this->field->source()}'",
                0,
                $e
            );
        }

        return $data;
    }

}
