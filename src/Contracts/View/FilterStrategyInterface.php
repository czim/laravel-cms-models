<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Builder;

interface FilterStrategyInterface
{

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string $strategy
     * @param string $key
     * @param mixed  $value
     * @return string
     */
    public function render($strategy, $key, $value);

    /**
     * Applies the filter value for
     *
     * @param Builder $query
     * @param string  $strategy
     * @param string  $key
     * @param mixed   $value
     */
    public function apply($query, $strategy, $key, $value);

}
