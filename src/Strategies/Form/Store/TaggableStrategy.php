<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\Model;

class TaggableStrategy extends AbstractFormFieldStoreStrategy
{

    /**
     * Retrieves current values from a model
     *
     * @param Model|Taggable $model
     * @param string         $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        if ($this->isTranslated()) {
            return $model->translations->mapWithKeys(function (Model $model) {
                /** @var Taggable $model */
                return [ $model->{config('translatable.locale_key', 'locale')} => $model->tagNames() ];
            });
        }

        return $model->tagNames();
    }

    /**
     * Adjusts a value for compatibility with taggable methods.
     *
     * @param mixed $value
     * @return array
     */
    protected function adjustValue($value)
    {
        if (is_string($value)) {
            $value = explode(';', $value);
        }

        if ( ! $value) {
            $value = [];
        }

        return array_filter($value);
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model|Taggable $model
     * @param mixed $source
     * @param mixed $value
     */
    public function performStoreAfter(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        $model->retag($value);
    }

}
