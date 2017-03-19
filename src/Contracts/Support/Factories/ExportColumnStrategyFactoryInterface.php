<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\Export\ExportColumnInterface;

interface ExportColumnStrategyFactoryInterface
{

    /**
     * Makes an export column display strategy instance.
     *
     * @param string $strategy
     * @return ExportColumnInterface
     */
    public function make($strategy);

}
