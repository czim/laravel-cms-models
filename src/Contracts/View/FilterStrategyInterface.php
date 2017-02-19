<?php
namespace Czim\CmsModels\Contracts\View;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

interface FilterStrategyInterface
{

    /**
     * Sets the filter's data.
     *
     * @param ModelFilterDataInterface $data
     * @return $this
     */
    public function setFilterInformation(ModelFilterDataInterface $data);

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string  $key
     * @param mixed   $value
     * @return string|View
     */
    public function render($key, $value);

    /**
     * Applies the filter strategy value to a query builder.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     */
    public function apply($query, $target, $value);

}
