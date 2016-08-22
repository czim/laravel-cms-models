<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface FilterStrategyInterface
{

    /**
     * Applies a strategy to render a filter field.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $key
     * @return string
     */
    public function render(Model $model, $strategy, $key);

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
