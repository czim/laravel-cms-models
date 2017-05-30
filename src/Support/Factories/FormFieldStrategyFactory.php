<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\FormFieldStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Strategies\FormFieldDisplayInterface;
use RuntimeException;

class FormFieldStrategyFactory extends AbstractStrategyFactory implements FormFieldStrategyFactoryInterface
{

    /**
     * Makes a form field display strategy instance.
     *
     * @param string $strategy
     * @return FormFieldDisplayInterface
     */
    public function make($strategy)
    {
        if ( ! $strategy) {
            return $this->getDefaultStrategy();
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            return app($strategyClass);
        }

        throw new RuntimeException("Could not create strategy instance for '{$strategy}'");
    }

    /**
     * @return FormFieldDisplayInterface
     */
    protected function getDefaultStrategy()
    {
        return app(config('cms-models.strategies.form.default-strategy'));
    }

    /**
     * Returns interface FQN for the strategy.
     *
     * @return string
     */
    protected function getStrategyInterfaceClass()
    {
        return FormFieldDisplayInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.form.aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.form.default-namespace';
    }
}
