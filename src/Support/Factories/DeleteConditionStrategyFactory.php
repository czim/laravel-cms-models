<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\DeleteConditionStrategyInterface;
use Czim\CmsModels\Contracts\Support\Factories\DeleteConditionStrategyFactoryInterface;
use RuntimeException;

class DeleteConditionStrategyFactory extends AbstractStrategyFactory implements DeleteConditionStrategyFactoryInterface
{

    /**
     * Makes a delete condition strategy instance.
     *
     * @param string $strategy
     * @return DeleteConditionStrategyInterface
     */
    public function make($strategy)
    {
        // If the strategy indicates the FQN of a delete condition strategy,
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
        return DeleteConditionStrategyInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.delete.condition-aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.delete.default-condition-namespace';
    }
}
