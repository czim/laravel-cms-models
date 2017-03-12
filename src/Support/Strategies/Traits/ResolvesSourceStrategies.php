<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Czim\CmsModels\Exceptions\StrategyResolutionException;
use Czim\CmsModels\Support\Data\Strategies\InstantiableClassStrategy;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

trait ResolvesSourceStrategies
{

    /**
     * Resolves and returns source content for a list strategy.
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    protected function resolveModelSource(Model $model, $source)
    {
        // If the strategy indicates a method to be called on the model itself, do so
        if ($method = $this->parseAsModelMethodStrategyString($source, $model)) {
            return $model->{$method}();
        }

        // If the strategy indicates an instantiable/callable 'class@method' combination
        if ($data = $this->parseAsInstantiableClassMethodStrategyString($source)) {

            $method   = $data['method'];
            $instance = $data['instance'];

            return $instance->{$method}($model);
        }

        // If the name matches a method name on the model, call it
        if (method_exists($model, $source)) {
            return $model->{$source}();
        }

        return $model->{$source};
    }

    /**
     * @param string $strategy
     * @param object $class     the object the method would be called on
     * @return false|string
     * @throws StrategyResolutionException
     */
    protected function parseAsModelMethodStrategyString($strategy, $class)
    {
        if ( ! starts_with($strategy, '@') || strlen($strategy) < 2) {
            return false;
        }

        $method = substr($strategy, 1);

        if ( ! method_exists($class, $method)) {
            throw new StrategyResolutionException(
                "Could not find strategy defined method '{$method}' on object '" . get_class($class) . "'"
            );
        }

        return $method;
    }

    /**
     * @param string $strategy
     * @return InstantiableClassStrategy|false
     */
    protected function parseAsInstantiableClassMethodStrategyString($strategy)
    {
        if ( ! preg_match('#^(?<class>.*)@(?<method>.*)$#', $strategy, $matches)) {
            return false;
        }

        $data = new InstantiableClassStrategy();

        $data->class  = $matches['class'];
        $data->method = $matches['method'];

        if ( ! class_exists($data->class)) {
            throw new StrategyResolutionException("Could not find strategy class '{$data->class}'");
        }

        $instance = app($data->class);

        if ( ! is_object($instance) || ! method_exists($instance, $data->method)) {
            throw new StrategyResolutionException(
                "Could not find strategy method '{$data->method}' on object '{$data->class}'"
            );
        }

        $data->instance = $instance;

        return $data;
    }

}
