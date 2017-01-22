<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyInterface;

interface FilterStrategyFactoryInterface
{

    /**
     * Makes a filter strategy instance.
     *
     * @param string                        $strategy
     * @param string|null                   $key
     * @param ModelFilterDataInterface|null $info
     * @return FilterStrategyInterface
     */
    public function make($strategy, $key = null, ModelFilterDataInterface $info = null);

}
