<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class DefaultStrategy implements FormFieldStoreStrategyInterface
{
    use ResolvesSourceStrategies;


    /**
     * @var array
     */
    protected $parameters = [];


    /**
     * Sets parameters to use for retrieving & storing.
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        if ($this->isTranslated()) {
            return $model->translations->pluck($source, config('translatable.locale_key', 'locale'))->toArray();
        }

        return $this->resolveModelSource($model, $source);
    }

    /**
     * Stores a submitted value on a model
     *
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    public function store(Model $model, $source, $value)
    {
        if ( ! $this->isTranslated()) {
            $this->performStore($model, $source, $value);
            return;
        }

        /** @var Model|Translatable $model */

        if ( ! is_array($value)) {
            throw new UnexpectedValueException("Value should be in per-locale array format for translatable data");
        }

        foreach ($value as $locale => $singleValue) {
            $this->performStore($model->translateOrNew($locale), $source, $singleValue);
        }
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        if (method_exists($model, $source)) {
            $model->{$source}($value);
            return;
        }

        $model->{$source} = $value;
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function storeAfter(Model $model, $source, $value)
    {
        if ( ! $this->isTranslated()) {
            $this->performStoreAfter($model, $source, $value);
            return;
        }

        /** @var Model|Translatable $model */

        if ( ! is_array($value)) {
            throw new UnexpectedValueException("Value should be in per-locale array format for translatable data");
        }

        foreach ($value as $locale => $singleValue) {
            $this->performStoreAfter($model->translateOrNew($locale), $source, $singleValue);
        }
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function performStoreAfter(Model $model, $source, $value)
    {
    }


    /**
     * Adjusts or normalizes a value before storing it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        return $value;
    }

    /**
     * @return bool
     */
    protected function isTranslated()
    {
        return in_array('translated', $this->parameters);
    }

    /**
     * @return bool
     */
    protected function isNullable()
    {
        return in_array('nullable', $this->parameters);
    }

}
