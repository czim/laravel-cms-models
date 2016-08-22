<?php
namespace Czim\CmsModels\View\Traits;

use Czim\CmsModels\Support\Data\Strategies\InstantiableClassStrategy;
use RuntimeException;

trait ResolvesStrategies
{

    /**
     * @param string $strategy
     * @param object $class     the object the method would be called on
     * @return false|string
     */
    public function parseAsModelMethodStrategyString($strategy, $class)
    {
        if ( ! starts_with($strategy, '@') || strlen($strategy) < 2) {
            return false;
        }

        $method = substr($strategy, 1);

        if ( ! method_exists($class, $method)) {
            throw new RuntimeException(
                "Could not find strategy defined method '{$method}' on object '" . get_class($class) . "'"
            );
        }

        return $method;
    }

    /**
     * @param string $strategy
     * @return InstantiableClassStrategy|false
     */
    public function parseAsInstantiableClassMethodStrategyString($strategy)
    {
        if ( ! preg_match('#^(?<class>.*)@(?<method>.*)$#', $strategy, $matches)) {
            return false;
        }

        $data = new InstantiableClassStrategy();

        $data->class  = $matches['class'];
        $data->method = $matches['method'];

        if ( ! class_exists($data->class)) {
            throw new RuntimeException("Could not find strategy class '{$data->class}'");
        }

        $instance = app($data->class);

        if ( ! is_object($instance) || ! method_exists($instance, $data->method)) {
            throw new RuntimeException(
                "Could not find strategy method '{$data->method}' on object '{$data->class}'"
            );
        }

        $data->instance = $instance;

        return $data;
    }

}
