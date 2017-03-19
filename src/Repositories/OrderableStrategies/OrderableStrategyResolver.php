<?php
namespace Czim\CmsModels\Repositories\OrderableStrategies;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\OrderableStrategyInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;

class OrderableStrategyResolver
{

    /**
     * Resolves and returns orderable strategy.
     *
     * @param ModelInformationInterface|ModelInformation $information
     * @return OrderableStrategyInterface
     */
    public function resolve(ModelInformationInterface $information)
    {
        // For now, we only have one strategy

        /** @var OrderableStrategyInterface $strategy */
        $strategy = app(ListifyStrategy::class);

        return $strategy;
    }

}
