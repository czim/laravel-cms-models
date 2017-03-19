<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\ListDisplayStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Strategies\ListDisplayInterface;
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

        $info = $this->getModelInformation();

        foreach ($info->list->columns as $key => $data) {

            $instance = $this->getListDisplayFactory()->make($data->strategy);

            // Feed any extra information we can gather to the instance
            $instance
                ->setListInformation($data)
                ->setOptions($data->options());

            if ($data->source) {
                if (isset($info->attributes[ $data->source ])) {
                    $instance->setAttributeInformation(
                        $info->attributes[ $data->source ]
                    );
                }
            }

            $instance->initialize($info->modelClass());

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
