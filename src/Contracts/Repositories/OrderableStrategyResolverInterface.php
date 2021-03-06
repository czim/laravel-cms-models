<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

interface OrderableStrategyResolverInterface
{

    /**
     * Resolves and returns orderable strategy.
     *
     * @param ModelInformationInterface $information
     * @return OrderableStrategyInterface
     */
    public function resolve(ModelInformationInterface $information);

}
