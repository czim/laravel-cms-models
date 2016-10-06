<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Model;

class DefaultStrategy implements FormFieldStoreStrategyInterface
{
    use ResolvesSourceStrategies;


    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
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
        if (method_exists($model, $source)) {
            $model->{$source}($value);
            return;
        }

        $model->{$source} = $value;
    }

}
