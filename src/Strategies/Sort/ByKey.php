<?php
namespace Czim\CmsModels\Strategies\Sort;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class ByKey
 *
 * Simple sort by the model's primary key.
 */
class ByKey extends AbstractSortStrategy
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

        $modelTable = $query->getModel()->getTable();
        $modelKey   = $query->getModel()->getKeyName();

        $query->orderBy("{$modelTable}.{$modelKey}", $direction);

        return $query;
    }

}
