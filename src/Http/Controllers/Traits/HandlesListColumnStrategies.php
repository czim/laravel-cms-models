<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\ListDisplayStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\ListDisplayInterface;
use Czim\CmsModels\Http\Controllers\BaseModelController;
use Czim\CmsModels\Support\Data\ModelInformation;

trait HandlesListColumnStrategies
{

    /**
     * Collects and returns strategy instances for list columns.
     *
     * @return ListDisplayInterface[]
     */
    protected function getListColumnStrategyInstances()
    {
        $instances = [];

        foreach ($this->getModelInformation()->list->columns as $key => $data) {

            $instance = $this->getListDisplayFactory()->make($data->strategy);

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
     * @return ListDisplayStrategyFactoryInterface
     */
    protected function getListDisplayFactory()
    {
        return app(ListDisplayStrategyFactoryInterface::class);
    }


    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    abstract protected function getModelInformation();

}
