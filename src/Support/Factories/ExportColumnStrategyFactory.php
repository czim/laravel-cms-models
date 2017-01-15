<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\ExportColumnStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Exporting\ExportColumnInterface;

class ExportColumnStrategyFactory extends AbstractStrategyFactory implements ExportColumnStrategyFactoryInterface
{

    /**
     * Makes an export column strategy instance.
     *
     * @param string $strategy
     * @return ExportColumnInterface
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
     * @return ExportColumnInterface
     */
    protected function getDefaultStrategy()
    {
        return app(config('cms-models.strategies.export.default-column-strategy'));
    }

    /**
     * Returns interface FQN for the strategy.
     *
     * @return string
     */
    protected function getStrategyInterfaceClass()
    {
        return ExportColumnInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.export.column-aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.export.default-column-namespace';
    }

}
