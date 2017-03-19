<?php
namespace Czim\CmsModels\Contracts\Strategies;

interface ContextStrategyInterface
{

    /**
     * Applies contextual setup to a query/model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $parameters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, array $parameters = []);

}
