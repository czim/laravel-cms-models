<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\ActionStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\ActionStrategyInterface;
use RuntimeException;

class ActionStrategyFactory extends AbstractStrategyFactory implements ActionStrategyFactoryInterface
{

    /**
     * Makes an action strategy instance.
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
     * Returns interface FQN for the strategy.
     *
     * @return string
     */
    protected function getStrategyInterfaceClass()
    {
        return ActionStrategyInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.list.action-aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.list.default-action-namespace';
    }
}
