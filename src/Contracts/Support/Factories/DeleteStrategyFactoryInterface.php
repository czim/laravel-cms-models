<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\DeleteStrategyInterface;

interface DeleteStrategyFactoryInterface
{

    /**
     * Makes a delete strategy instance.
     *
     * @param string $strategy
     * @return DeleteStrategyInterface
     */
    public function make($strategy);

}
