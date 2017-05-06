<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\DeleteStrategyInterface;
use Czim\CmsModels\Contracts\Support\Factories\DeleteStrategyFactoryInterface;
use RuntimeException;

class DeleteStrategyFactory extends AbstractStrategyFactory implements DeleteStrategyFactoryInterface
{

    /**
     * Makes a delete strategy instance.
     *
     * @param string $strategy
     * @return DeleteStrategyInterface
     */
    public function make($strategy)
    {
        // If the strategy indicates the FQN of a delete strategy,
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
        return DeleteStrategyInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.delete.aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.delete.default-namespace';
    }
}
