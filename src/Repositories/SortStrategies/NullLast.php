<?php
namespace Czim\CmsModels\Repositories\SortStrategies;

use Czim\CmsModels\Contracts\Repositories\SortStrategyInterface;
use DB;
use Illuminate\Database\Eloquent\Builder;

class NullLast implements SortStrategyInterface
{

    /**
     * Applies the sort to a query/model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $direction     asc|desc
     * @return Builder
     */
    public function apply($query, $column, $direction = 'asc')
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        return $query->orderBy(DB::raw("IF(`{$column}` IS NULL,1,0)"))
                     ->orderBy($column, $direction);
    }

}
