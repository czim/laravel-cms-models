<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Czim\CmsModels\Contracts\Repositories\ContextStrategyInterface;

trait ResolvesRepositoryContextStrategy
{

    /**
     * Resolves the context strategy, if possible.
     *
     * @param $strategy
     * @return ContextStrategyInterface|null
     */
    protected function resolveContextStrategy($strategy)
    {
        if ( ! $strategy) {
            $strategy = config('cms-models.strategies.repository.default-strategy');
        }

        if ( ! ($strategyClass = $this->resolveContextStrategyClass($strategy))) {
            return null;
        }

        return app($strategyClass);
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a sort interface implementation,
     * or a configured alias.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveContextStrategyClass($strategy)
    {
        if ( ! empty($strategy)) {

            if ( ! str_contains($strategy, '.')) {
                $strategy = config('cms-models.strategies.repository.aliases.' . $strategy, $strategy);
            }

            if (class_exists($strategy) && is_a($strategy, ContextStrategyInterface::class, true)) {
                return $strategy;
            }

            $strategy = $this->prefixContextStrategyNamespace($strategy);

            if (class_exists($strategy) && is_a($strategy, ContextStrategyInterface::class, true)) {
                return $strategy;
            }
        }

        return false;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixContextStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.repository.default-namespace'), '\\') . '\\' . $class;
    }

}
