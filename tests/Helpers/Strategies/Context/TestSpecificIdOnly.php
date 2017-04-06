<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\Context;

use Czim\CmsModels\Contracts\Strategies\ContextStrategyInterface;

class TestSpecificIdOnly implements ContextStrategyInterface
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
        return $query->where('id', 2);
    }
}
