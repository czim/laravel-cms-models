<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Builder;

interface FilterApplicationInterface
{

    /**
     * Applies the filter strategy value to a query builder.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     */
    public function apply($query, $target, $value);

}
