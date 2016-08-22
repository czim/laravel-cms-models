<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyResolverInterface;
use Czim\CmsModels\View\Traits\ResolvesStrategies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FilterStrategy implements FilterStrategyInterface
{
    use ResolvesStrategies;

    /**
     * @var FilterStrategyResolverInterface
     */
    protected $resolver;


    /**
     * @param FilterStrategyResolverInterface $resolver
     */
    public function __construct(FilterStrategyResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }


    /**
     * Applies a strategy to render a filter field.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $key
     * @return string
     */
    public function render(Model $model, $strategy, $key)
    {
        // Resolve strategy if possible
        $resolved = $this->resolver->resolve($strategy);

        if ($resolved) {
            $strategy = $resolved;
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            /** @var FilterStrategyInterface $instance */
            $instance = app($strategyClass);

            return $instance->render($model, $strategy, $key);
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
     * Applies the filter value for
     *
     * @param Builder $query
     * @param string  $strategy
     * @param string  $key
     * @param mixed   $value
     */
    public function apply($query, $strategy, $key, $value)
    {
        // todo
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a filter display interface implementation.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if (class_exists($strategy) && is_a($strategy, FilterStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, FilterStrategyInterface::class, true)) {
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
        return rtrim(config('cms-models.strategies.filter.default-namespace'), '\\') . '\\' . $class;
    }

}
