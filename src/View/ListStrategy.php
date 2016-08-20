<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\View\ListDisplayInterface;
use Czim\CmsModels\Contracts\View\ListStrategyInterface;
use Czim\CmsModels\Contracts\View\ListStrategyResolverInterface;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ListStrategy implements ListStrategyInterface
{

    /**
     * @var ListStrategyResolverInterface
     */
    protected $resolver;


    /**
     * @param ListStrategyResolverInterface $resolver
     */
    public function __construct(ListStrategyResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }


    /**
     * Applies a strategy to renders a list value from its source.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source
     * @return string
     */
    public function render(Model $model, $strategy, $source)
    {
        // Resolve strategy if possible
        $resolved = $this->resolver->resolve($strategy);

        if ($resolved) {
            $strategy = $resolved;
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            /** @var ListDisplayInterface $instance */
            $instance = app($strategyClass);

            return $instance->render($model, $source);
        }

        // If the strategy indicates a method to be called on the model itself, do so
        if (starts_with($strategy, '@')) {

            $method = substr($strategy, 1);

            if ( ! method_exists($model, $method)) {
                throw new RuntimeException(
                    "Could not find list strategy method '{$method}' on Model '" . get_class($model) . "'"
                );
            }

            return $model->{$method}($model->{$source});
        }

        // If the strategy indicates an instantiable/callable 'class@method' combination
        if (preg_match('#^(?<class>.*)@(?<method>.*)$#', $strategy, $matches)) {

            $class  = $matches['class'];
            $method = $matches['method'];

            if ( ! class_exists($class)) {
                throw new RuntimeException("Could not find list strategy class '{$class}'");
            }

            $instance = app($class);

            if ( ! is_object($instance) || ! method_exists($instance, $method)) {
                throw new RuntimeException("Could not find list strategy method '{$method}' on '{$class}'");
            }

            return $instance->{$method}($model->{$source});
        }

        // If nothing special is defined, simply return the source value
        return $model->{$source};
    }

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source source column, method name or value
     * @return null|string
     */
    public function style(Model $model, $strategy, $source)
    {
        // Resolve strategy if possible
        $resolved = $this->resolver->resolve($strategy);

        if ($resolved) {
            $strategy = $resolved;
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            /** @var ListDisplayInterface $instance */
            $instance = app($strategyClass);

            return $instance->style($model, $source);
        }

        return null;
    }

    /**
     * Returns an optional set of attribute values to merge into the list display value container.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source source column, method name or value
     * @return array associative, key value pairs with html tag attributes
     */
    public function attributes(Model $model, $strategy, $source)
    {
        // Resolve strategy if possible
        $resolved = $this->resolver->resolve($strategy);

        if ($resolved) {
            $strategy = $resolved;
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            /** @var ListDisplayInterface $instance */
            $instance = app($strategyClass);

            return $instance->attributes($model, $source);
        }

        return [];
    }


    /**
     * Resolves strategy assuming it is the class name or FQN of a list display interface implementation.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if (class_exists($strategy) && is_a($strategy, ListDisplayInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, ListDisplayInterface::class, true)) {
            return $strategy;
        }

        return false;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.list.default-namespace'), '\\') . '\\' . $class;
    }

}
