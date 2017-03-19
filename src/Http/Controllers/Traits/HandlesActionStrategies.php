<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\ActionStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Strategies\ActionStrategyInterface;
use Czim\CmsModels\Http\Controllers\BaseModelController;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;

trait HandlesActionStrategies
{

    /**
     * Collects and returns (permitted) strategy instance for the default row click action.
     *
     * @return ActionStrategyInterface|false
     */
    protected function getDefaultRowActionInstance()
    {
        // Make the first action that is allowed for the current user
        foreach ($this->getModelInformation()->list->default_action as $data) {

            $permissions = $data->permissions();

            if ( ! empty($permissions) && ! cms_auth()->can($permissions)) {
                continue;
            }

            $instance = $this->getActionStrategyFactory()->make($data->strategy);

            // Initialize the instance with extra information
            $instance->initialize($data, $this->getModelInformation()->modelClass());

            return $instance;
        }

        return false;
    }

    /**
     * @return ActionStrategyFactoryInterface
     */
    protected function getActionStrategyFactory()
    {
        return app(ActionStrategyFactoryInterface::class);
    }


    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    abstract protected function getModelInformation();

}
