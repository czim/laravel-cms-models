<?php
namespace Czim\CmsModels\Contracts\Strategies;

interface SortStrategyInterface
{

    /**
     * Applies the sort to a query/model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $direction     asc|desc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, $column, $direction = 'asc');

}
