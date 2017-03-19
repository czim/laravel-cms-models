<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\OrderableStrategyResolverInterface;
use Illuminate\Database\Eloquent\Model;

trait SetsModelOrderablePosition
{

    /**
     * Changes the orderable position for a model.
     *
     * @param Model           $model
     * @param string|int|null $position
     * @return bool     the new position of the model
     */
    protected function changeModelOrderablePosition(Model $model, $position)
    {
        /** @var OrderableStrategyResolverInterface $resolver */
        $resolver = app(OrderableStrategyResolverInterface::class);

        $strategy = $resolver->resolve($this->getModelInformation());

        return $strategy->setPosition($model, $position);
    }


    /**
     * @return ModelInformationInterface
     */
    abstract protected function getModelInformation();

}
