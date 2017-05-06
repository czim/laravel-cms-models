<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\DeleteConditionStrategyInterface;

interface DeleteConditionStrategyFactoryInterface
{

    /**
     * Makes a delete condition strategy instance.
     *
     * @param string $strategy
     * @return DeleteConditionStrategyInterface
     */
    public function make($strategy);

}
