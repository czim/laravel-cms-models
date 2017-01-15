<?php
namespace Czim\CmsModels\Support\Factories;

abstract class AbstractStrategyFactory
{

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
            $strategy = config($this->getAliasesBaseConfigKey() . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, $this->getStrategyInterfaceClass(), true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, $this->getStrategyInterfaceClass(), true)) {
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
        return rtrim(config($this->getNamespaceConfigKey()), '\\') . '\\' . $class;
    }

    /**
     * Returns interface FQN for the strategy.
     *
     * @return string
     */
    abstract protected function getStrategyInterfaceClass();

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    abstract protected function getAliasesBaseConfigKey();

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    abstract protected function getNamespaceConfigKey();

}
