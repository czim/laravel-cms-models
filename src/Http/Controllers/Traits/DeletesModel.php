<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\DeleteStrategyFactoryInterface;
use Illuminate\Database\Eloquent\Model;

trait DeletesModel
{

    /**
     * Deletes model.
     *
     * Note that this does not check authorization, conditions, etc.
     *
     * @param Model $model
     * @return bool
     */
    protected function deleteModel(Model $model)
    {
        $strategy = $this->getModelInformation()->deleteStrategy();

        if ( ! $strategy) {
            return $model->delete();
        }

        /** @var DeleteStrategyFactoryInterface $factory */
        $factory = app(DeleteStrategyFactoryInterface::class);
        $strategy = $factory->make($strategy);

        return $strategy->delete($model);
    }

    /**
     * @return ModelInformationInterface
     */
    abstract protected function getModelInformation();

}
