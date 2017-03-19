<?php
namespace Czim\CmsModels\Repositories\ActivateStrategies;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ActivateStrategyInterface;
use Czim\CmsModels\Contracts\Repositories\ActivateStrategyResolverInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;

class ActivateStrategyResolver implements ActivateStrategyResolverInterface
{

    /**
     * Resolves and returns activate strategy.
     *
     * @param ModelInformationInterface|ModelInformation $information
     * @return ActivateStrategyInterface
     */
    public function resolve(ModelInformationInterface $information)
    {
        // For now, we only have one strategy

        /** @var ActivateStrategyInterface $strategy */
        $strategy = app(ActiveColumn::class);

        if ($information->list->active_column && method_exists($strategy, 'setColumn')) {
            $strategy->setColumn($information->list->active_column);
        }

        return $strategy;
    }

}
