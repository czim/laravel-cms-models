<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ActivateStrategyResolverInterface;
use Illuminate\Database\Eloquent\Model;

trait SetsModelActivateState
{

    /**
     * Changes the active state for a model.
     *
     * @param Model $model
     * @param bool  $active
     * @return bool     the current active state of the model
     */
    protected function changeModelActiveState(Model $model, $active)
    {
        /** @var ActivateStrategyResolverInterface $resolver */
        $resolver = app(ActivateStrategyResolverInterface::class);

        $strategy = $resolver->resolve($this->getModelInformation());

        return $strategy->update($model, $active);
    }


    /**
     * @return ModelInformationInterface
     */
    abstract protected function getModelInformation();

}
