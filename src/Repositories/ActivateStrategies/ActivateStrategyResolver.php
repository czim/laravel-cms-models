<?php
namespace Czim\CmsModels\Repositories\ActivateStrategies;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ActivateStrategyInterface;
use Czim\CmsModels\Support\Data\ModelInformation;

class ActivateStrategyResolver
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
