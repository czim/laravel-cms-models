<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Contracts\Support\Factories\FormStoreStrategyFactoryInterface;
use RuntimeException;

class FormStoreStrategyFactory extends AbstractStrategyFactory implements FormStoreStrategyFactoryInterface
{

    /**
     * Makes a form store display strategy instance.
     *
     * @param string $strategy
     * @return FormFieldStoreStrategyInterface
     */
    public function make($strategy)
    {
        if ( ! $strategy) {
            return $this->getDefaultStrategy();
        }

        // If the strategy indicates the FQN of store strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            return app($strategyClass);
        }

        throw new RuntimeException("Could not create strategy instance for '{$strategy}'");
    }

    /**
     * @return FormFieldStoreStrategyInterface
     */
    protected function getDefaultStrategy()
    {
        return app(config('cms-models.strategies.form.default-store-strategy'));
    }

    /**
     * Returns interface FQN for the strategy.
     *
     * @return string
     */
    protected function getStrategyInterfaceClass()
    {
        return FormFieldStoreStrategyInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.form.store-aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.form.default-store-namespace';
    }
}
