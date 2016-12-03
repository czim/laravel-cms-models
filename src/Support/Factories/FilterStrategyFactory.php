<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\View\FilterApplicationInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;
use RuntimeException;

class FilterStrategyFactory
{

    /**
     * Makes a filter display instance.
     *
     * @param string                        $strategy
     * @param string|null                   $key
     * @param ModelFilterDataInterface|null $info
     * @return FilterDisplayInterface
     */
    public function makeForDisplay($strategy, $key = null, ModelFilterDataInterface $info = null)
    {
        // A filter must have a resolvable strategy for displaying
        if ( ! ($strategyClass = $this->resolveDisplayStrategyClass($strategy))) {
            throw new RuntimeException(
                "Could not resolve display strategy class for {$key}: '{$strategy}'"
            );
        }

        /** @var FilterDisplayInterface $instance */
        $instance = app($strategyClass);

        // todo: set info on instance

        return $instance;
    }

    /**
     * Make a filter application instance.
     *
     * @param string                        $strategy
     * @param string|null                   $key
     * @param ModelFilterDataInterface|null $info
     * @return FilterApplicationInterface
     */
    public function makeForApplication($strategy, $key = null, ModelFilterDataInterface $info = null)
    {
        /** @var FilterApplicationInterface $instance */
        $instance = $this->makeForDisplay($strategy, $key, $info);

        return $instance;
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
