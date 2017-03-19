<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\ActionStrategyInterface;

interface ActionStrategyFactoryInterface
{

    /**
     * Makes a action strategy instance.
     *
     * @param string $strategy
     * @return ActionStrategyInterface
     */
    public function make($strategy);

}
