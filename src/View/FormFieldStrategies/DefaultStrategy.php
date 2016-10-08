<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\View\FormFieldDisplayInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Illuminate\Database\Eloquent\Model;

class DefaultStrategy implements FormFieldDisplayInterface
{

    /**
     * Renders a form field.
     *
     * @param Model                                          $model
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @param mixed                                          $value
     * @param array                                          $errors
     * @return string
     */
    public function render(Model $model, ModelFormFieldDataInterface $field, $value, array $errors = [])
    {
        return view('cms-models::model.partials.form.strategies.default', [
            'record' => $model,
            'key'    => $field->key(),
            'value'  => old($field->key(), $value),
            'type'   => $field->type ?: 'text',
            'errors' => $errors,
        ]);
    }
}
