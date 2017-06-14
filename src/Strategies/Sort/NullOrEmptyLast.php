<?php
namespace Czim\CmsModels\Strategies\Sort;

use DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class NullOrEmptyLast
 *
 * Sorts any value that is NULL or equal to an empty string below all else.
 */
class NullOrEmptyLast extends NullLast
{

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
            $query = $query->orderBy(DB::raw("IF(`{$column}` IS NULL OR `{$column}` = '',1,0)"));
        }

        return $query;
    }

}
