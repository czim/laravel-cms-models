<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PasswordUpdateStrategy
 *
 * For setting a new hashed password, but only if a value is submitted.
 * Also returns null as value no matter what is currently set.
 */
class PasswordUpdateStrategy extends AbstractFormFieldStoreStrategy
{

    /**
     * {@inheritdoc}
     */
    public function retrieve(Model $model, $source)
    {
        if ($this->isTranslated()) {
            $keys = $model->translations->pluck(config('translatable.locale_key', 'locale'))->toArray();

            $nulls = [];
            foreach ($keys as $locale) {
                $nulls[ $locale ] = null;
            }
            return $nulls;
        }

        return null;
    }

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
