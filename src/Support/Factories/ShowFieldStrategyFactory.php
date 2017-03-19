<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\ShowFieldStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Strategies\ShowFieldInterface;

class ShowFieldStrategyFactory implements ShowFieldStrategyFactoryInterface
{

    /**
     * Makes a show field strategy instance.
     *
     * @param string $strategy
     * @return ShowFieldInterface
     */
    public function make($strategy)
    {
        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            return app($strategyClass);
        }

        return $this->getDefaultStrategy();
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a show field interface
     * implementation or an alias for one.
     *
     * @param string $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config(
                'cms-models.strategies.show.aliases.' . $strategy,
                config('cms-models.strategies.list.aliases.' . $strategy)
            );
        }

        if (class_exists($strategy) && is_a($strategy, ShowFieldInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, ShowFieldInterface::class, true)) {
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
        if (config('cms-models.strategies.show.default-namespace')) {
            return rtrim(config('cms-models.strategies.show.default-namespace'), '\\') . '\\' . $class;
        }

        return rtrim(config('cms-models.strategies.list.default-namespace'), '\\') . '\\' . $class;
    }

    /**
     * @return ShowFieldInterface
     */
    protected function getDefaultStrategy()
    {
        if ($strategy = config('cms-models.strategies.show.default-strategy')) {
            return app(config('cms-models.strategies.show.default-strategy'));
        }

        return app(config('cms-models.strategies.list.default-strategy'));
    }

}
