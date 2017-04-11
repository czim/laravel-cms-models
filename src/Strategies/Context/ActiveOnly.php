<?php
namespace Czim\CmsModels\Strategies\Context;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Strategies\ContextStrategyInterface;
use Illuminate\Database\Eloquent\Model;

class ActiveOnly implements ContextStrategyInterface
{

    /**
     * Applies contextual setup to a query/model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $parameters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, array $parameters = [])
    {
        $info = $this->getModelInformation($query->getModel());

        $column = $info && $info->list->active_column ?: 'active';

        return $query->where($column, true);
    }

    protected function getModelInformation(Model $model)
    {
        /** @var ModelInformationRepositoryInterface $repository */
        $repository = app(ModelInformationRepositoryInterface::class);

        return $repository->getByModel($model);
    }

}
