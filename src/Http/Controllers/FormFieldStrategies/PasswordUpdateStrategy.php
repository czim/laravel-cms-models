<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PasswordUpdateStrategy
 *
 * For setting a new hashed password, but only if a value is submitted.
 */
class PasswordUpdateStrategy extends AbstractFormFieldStoreStrategy
{

    /**
     * {@inheritdoc}
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        // Do not change anything if the value is not set.
        if ( ! $value) {
            return;
        }

        // Otherwise, hash the value
        $value = \Hash::make($value);

        if (method_exists($model, $source)) {
            $model->{$source}($value);
            return;
        }

        $model->{$source} = $value;
    }

}
