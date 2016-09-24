<?php
namespace Czim\CmsModels\Repositories\SortStrategies;

use DB;
use Illuminate\Database\Eloquent\Builder;

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

        $supportsIf = $this->databaseSupportsIf($query);

        if ($supportsIf) {
            $query = $query->orderBy(DB::raw("IF(`{$column}` IS NULL,1,0)"));
        }

        return $query->orderBy($column, $direction);
    }

}
