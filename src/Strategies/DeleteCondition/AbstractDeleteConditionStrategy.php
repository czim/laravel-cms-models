<?php
namespace Czim\CmsModels\Strategies\DeleteCondition;

use Czim\CmsModels\Contracts\Strategies\DeleteConditionStrategyInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractDeleteConditionStrategy implements DeleteConditionStrategyInterface
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Returns whether deletion is allowed.
     *
     * @param Model $model
     * @param array $parameters strategy-dependent parameters
     * @return mixed
     */
    public function check(Model $model, array $parameters = [])
    {
        $this->model      = $model;
        $this->parameters = $parameters;

        return $this->performCheck();
    }

    /**
     * Returns whether deletion is allowed.
     *
     * @return bool
     */
    abstract protected function performCheck();


    /**
     * @param Model|null $model
     * @return \Czim\CmsModels\Support\Data\ModelInformation|false
     */
    protected function getModelInformation(Model $model = null)
    {
        $model = $model ?: $this->model;

        return $this->getModelInformationRepository()->getByModel($model);
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }
}
