<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\ShowFieldInterface;

interface ShowFieldStrategyFactoryInterface
{

    /**
     * Makes a show field strategy instance.
     *
     * @param string $strategy
     * @return ShowFieldInterface
     */
    public function make($strategy);

}
