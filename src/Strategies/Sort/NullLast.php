<?php
namespace Czim\CmsModels\Strategies\Sort;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

        $supportsIf = $this->databaseSupportsIf($query);

        if ($supportsIf) {
            $query = $query->orderBy(DB::raw("IF(`{$column}` IS NULL OR `{$column}` = '',1,0)"));
        }

        return $query->orderBy($column, $direction);
    }

}
