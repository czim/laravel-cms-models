<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\View\ListDisplayInterface;
use Czim\CmsModels\Contracts\View\ListStrategyInterface;
use Czim\CmsModels\Contracts\View\ListStrategyResolverInterface;
use Czim\CmsModels\View\Traits\ResolvesStrategies;
use Illuminate\Database\Eloquent\Model;

class ListStrategy implements ListStrategyInterface
{
    use ResolvesStrategies;

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
     * Applies a strategy to render a list value from its source.
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
        if ($method = $this->parseAsModelMethodStrategyString($strategy, $model)) {

            return $model->{$method}($model->{$source});
        }

        // If the strategy indicates an instantiable/callable 'class@method' combination
        if ($data = $this->parseAsInstantiableClassMethodStrategyString($strategy)) {

            $method   = $data['method'];
            $instance = $data['instance'];

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
