<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\View\ListDisplayInterface;
use Czim\CmsModels\Http\Controllers\BaseModelController;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;

trait HandlesListColumnStrategies
{
    use ResolvesSourceStrategies;

    /**
     * Collects and returns strategy instances for list columns.
     *
     * @return ListDisplayInterface[]
     */
    protected function getListColumnStrategyInstances()
    {
        $instances = [];

        foreach ($this->getModelInformation()->list->columns as $key => $data) {

            $instance = $this->makeListColumnStrategyInstance($data->strategy);

            // Feed any extra information we can gather to the instance
            $instance->setListInformation($data);

            if ($data->source) {
                if (isset($this->getModelInformation()->attributes[ $data->source ])) {
                    $instance->setAttributeInformation(
                        $this->getModelInformation()->attributes[ $data->source ]
                    );
                }
            }

            $instances[ $key ] = $instance;
        }

        return $instances;
    }

    /**
     * Makes a list column display strategy instance for a given strategy string.
     *
     * @param string $strategy
     * @return ListDisplayInterface
     */
    protected function makeListColumnStrategyInstance($strategy)
    {
        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveListColumnStrategyClass($strategy)) {

            return app($strategyClass);
        }

        return $this->getDefaultListColumnDisplayStrategy();
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a list display interface
     * implementation or an alias for one.
     *
     * @param string $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveListColumnStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.list.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, ListDisplayInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixListColumnStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, ListDisplayInterface::class, true)) {
            return $strategy;
        }

        return false;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixListColumnStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.list.default-namespace'), '\\') . '\\' . $class;
    }

    /**
     * @return ListDisplayInterface
     */
    protected function getDefaultListColumnDisplayStrategy()
    {
        return app(config('cms-models.strategies.list.default-strategy'));
    }


    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    abstract protected function getModelInformation();

}
