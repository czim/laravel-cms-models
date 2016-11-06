<?php
namespace Czim\CmsModels\Contracts\View;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Illuminate\Database\Eloquent\Model;

interface FormFieldDisplayInterface
{

    /**
     * Renders a form field.
     *
     * @param Model                       $model
     * @param ModelFormFieldDataInterface $field
     * @param mixed                       $value            the current or old() value
     * @param mixed                       $originalValue    the persisted model's current value
     * @param array                       $errors
     * @return string
     */
    public function render(
        Model $model,
        ModelFormFieldDataInterface $field,
        $value,
        $originalValue,
        array $errors = []
    );

}
