<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Support\Factories\ExportStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Strategies\Export\ModelExporterInterface;
use UnexpectedValueException;

class ExportStrategyFactory extends AbstractStrategyFactory implements ExportStrategyFactoryInterface
{

    /**
     * Makes an export column strategy instance.
     *
     * @param string $strategy
     * @return ModelExporterInterface
     */
    public function make($strategy)
    {
        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            return app($strategyClass);
        }

        throw new UnexpectedValueException("No or unknown strategy given for exporting: '{$strategy}'");
    }

    /**
     * Returns interface FQN for the strategy.
     *
     * @return string
     */
    protected function getStrategyInterfaceClass()
    {
        return ModelExporterInterface::class;
    }

    /**
     * Returns the configuration key for the aliases map.
     *
     * @return string
     */
    protected function getAliasesBaseConfigKey()
    {
        return 'cms-models.strategies.export.aliases.';
    }

    /**
     * Returns the configuration key for the default namespace.
     *
     * @return string
     */
    protected function getNamespaceConfigKey()
    {
        return 'cms-models.strategies.export.default-namespace';
    }

}
