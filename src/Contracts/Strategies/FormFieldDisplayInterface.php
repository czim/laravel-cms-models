<?php
namespace Czim\CmsModels\Contracts\Strategies;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Illuminate\Contracts\View\View;
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
     * @return string|View
     */
    public function render(
        Model $model,
        ModelFormFieldDataInterface $field,
        $value,
        $originalValue,
        array $errors = []
    );

}
