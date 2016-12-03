<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\ListDisplayStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\ListDisplayInterface;

class ListDisplayStrategyFactory implements ListDisplayStrategyFactoryInterface
{

    /**
     * Makes a list column display strategy instance.
     *
     * @param string $strategy
     * @return ListDisplayInterface
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
     * Resolves strategy assuming it is the class name or FQN of a list display interface
     * implementation or an alias for one.
     *
     * @param string $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.list.aliases.' . $strategy, $strategy);
        }

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

    /**
     * @return ListDisplayInterface
     */
    protected function getDefaultStrategy()
    {
        return app(config('cms-models.strategies.list.default-strategy'));
    }

}
