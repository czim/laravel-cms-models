<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\FilterStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;
use Czim\CmsModels\Http\Controllers\BaseModelController;
use Czim\CmsModels\Support\Data\ModelInformation;

trait HandlesFilterStrategies
{

    /**
     * Collects and returns (display) strategy instances for filters.
     *
     * @return FilterDisplayInterface[]
     */
    protected function getFilterStrategyInstances()
    {
        if ($this->getModelInformation()->list->disable_filters) {
            return [];
        }

        $instances = [];

        foreach ($this->getModelInformation()->list->filters as $key => $data) {

            $instances[ $key ] = $this->getFilterFactory()->makeForDisplay($data->strategy, $key, $data);
        }

        return $instances;
    }


    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    abstract protected function getModelInformation();

    /**
     * @return FilterStrategyFactoryInterface
     */
    protected function getFilterFactory()
    {
        return app(FilterStrategyFactoryInterface::class);
    }

}
