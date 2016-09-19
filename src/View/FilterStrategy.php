<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\View\FilterApplicationInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyResolverInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class FilterStrategy implements FilterStrategyInterface
{
    use ResolvesSourceStrategies;

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
     * @param string  $strategy
     * @param string  $key
     * @param mixed   $value
     * @param ModelFilterDataInterface|ModelListFilterData $info
     * @return string
     */
    public function render($strategy, $key, $value, ModelFilterDataInterface $info)
    {
        // Resolve strategy if possible
        $resolved = $this->resolver->resolve($strategy);

        if ($resolved) {
            $strategy = $resolved;
        }

        // A filter must have a resolvable strategy for displaying
        if ( ! ($strategyClass = $this->resolveDisplayStrategyClass($strategy))) {
            throw new RuntimeException(
                "Could not resolve display strategy class for {$key}: '{$strategy}'"
            );
        }

        /** @var FilterDisplayInterface $instance */
        $instance = app($strategyClass);

        return $instance->render($key, $value, $info);
    }

    /**
     * Applies the filter value for a strategy to a query builder.
     *
     * @param Builder $query
     * @param string  $strategy
     * @param string  $target
     * @param mixed   $value
     */
    public function apply($query, $strategy, $target, $value)
    {
        // A filter must have a resolvable strategy for applying
        if ( ! ($strategyClass = $this->resolveApplicationStrategyClass($strategy))) {
            throw new RuntimeException(
                "Could not resolve application strategy class for {$target}: '{$strategy}'"
            );
        }

        /** @var FilterApplicationInterface $instance */
        $instance = app($strategyClass);

        return $instance->apply($query, $target, $value);
    }


    /**
     * Resolves display strategy assuming it is the class name or FQN of a filter interface implementation.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveDisplayStrategyClass($strategy)
    {
        return $this->resolveStrategyClass($strategy, FilterDisplayInterface::class);
    }

    /**
     * Resolves application strategy assuming it is the class name or FQN of a filter interface implementation.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveApplicationStrategyClass($strategy)
    {
        return $this->resolveStrategyClass($strategy, FilterApplicationInterface::class);
    }

    /**
     * Resolves a filter strategy class.
     *
     * @param $strategy
     * @param $interfaceFqn
     * @return bool|string
     */
    protected function resolveStrategyClass($strategy, $interfaceFqn)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.filter.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, $interfaceFqn, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, $interfaceFqn, true)) {
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
