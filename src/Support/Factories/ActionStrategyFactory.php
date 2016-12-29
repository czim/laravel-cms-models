<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\ActionStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\ActionStrategyInterface;
use RuntimeException;

class ActionStrategyFactory implements ActionStrategyFactoryInterface
{

    /**
     * Makes a action strategy instance.
     *
     * @param string $strategy
     * @return ActionStrategyInterface
     */
    public function make($strategy)
    {
        // If the strategy indicates the FQN of action strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            return app($strategyClass);
        }

        throw new RuntimeException("Could not create strategy instance for '{$strategy}'");
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of an action strategy interface
     * implementation or an alias for one.
     *
     * @param string $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if (empty($strategy)) {
            return false;
        }

        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.list.action-aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, ActionStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, ActionStrategyInterface::class, true)) {
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
        return rtrim(config('cms-models.strategies.list.default-action-namespace'), '\\') . '\\' . $class;
    }

}
