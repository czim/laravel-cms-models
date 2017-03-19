<?php
namespace Czim\CmsModels\Strategies\Sort;

use Czim\CmsModels\Contracts\Strategies\SortStrategyInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\MySqlConnection;

abstract class AbstractSortStrategy implements SortStrategyInterface
{

    /**
     * Returns whether the current database driver supports the IF function.
     *
     * @param Builder $query
     * @return bool
     */
    protected function databaseSupportsIf(Builder $query)
    {
        return $query->getQuery()->getConnection() instanceof MySqlConnection;
    }

}
