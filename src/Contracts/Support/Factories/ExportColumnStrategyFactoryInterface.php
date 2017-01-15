<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Support\Exporting\ExportColumnInterface;

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
