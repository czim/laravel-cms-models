<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\ListDisplayStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Strategies\ListDisplayInterface;

class ListDisplayStrategyFactory extends AbstractStrategyFactory implements ListDisplayStrategyFactoryInterface
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
     * @return ListDisplayInterface
     */
    protected function getDefaultStrategy()
    {
        return app(config('cms-models.strategies.list.default-strategy'));
    }

    /**
     * Returns interface FQN for the strategy.
     *
     * @return string
     */
    protected function getStrategyInterfaceClass()
    {
        return ListDisplayInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.list.aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.list.default-namespace';
    }
}
