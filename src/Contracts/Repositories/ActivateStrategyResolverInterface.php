<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

interface ActivateStrategyResolverInterface
{

    /**
     * Resolves and returns activate strategy.
     *
     * @param ModelInformationInterface $information
     * @return ActivateStrategyInterface
     */
    public function resolve(ModelInformationInterface $information);

}
