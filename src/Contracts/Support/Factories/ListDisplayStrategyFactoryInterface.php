<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\ListDisplayInterface;

interface ListDisplayStrategyFactoryInterface
{

    /**
     * Makes a list column display strategy instance.
     *
     * @param string $strategy
     * @return ListDisplayInterface
     */
    public function make($strategy);

}
