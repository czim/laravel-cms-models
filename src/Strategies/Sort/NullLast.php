<?php
namespace Czim\CmsModels\Strategies\Sort;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NullLast
 *
 * Sorts only strict NULL values after all else.
 */
class NullLast extends AbstractSortStrategy
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

        if ($query instanceof Model) {
            $query = $query->query();
        }

        $query = $this->applyNullLastQuery($query, $column);

        return $query->orderBy($column, $direction);
    }

    /**
     * Applies logic to query builder to sort null or empty fields last.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    protected function applyNullLastQuery($query, $column)
    {
        if ($this->databaseSupportsIf($query)) {
            $query = $query->orderBy(DB::raw("IF(`{$column}` IS NULL,1,0)"));
        }

        return $query;
    }

}
