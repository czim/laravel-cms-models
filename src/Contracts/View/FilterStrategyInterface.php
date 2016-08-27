<?php
namespace Czim\CmsModels\Contracts\View;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Database\Eloquent\Builder;

interface FilterStrategyInterface
{

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string  $strategy
     * @param string  $key
     * @param mixed   $value
     * @param ModelFilterDataInterface|ModelListFilterData $info
     * @return string
     */
    public function render($strategy, $key, $value, ModelFilterDataInterface $info);

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
