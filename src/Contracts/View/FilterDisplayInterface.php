<?php
namespace Czim\CmsModels\Contracts\View;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;

interface FilterDisplayInterface
{

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string  $key
     * @param mixed   $value
     * @param ModelFilterDataInterface|ModelListFilterData $info
     * @return string
     */
    public function render($key, $value, ModelFilterDataInterface $info);

}
