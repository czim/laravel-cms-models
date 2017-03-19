<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\Export\ModelExporterInterface;

interface ExportStrategyFactoryInterface
{

    /**
     * Makes an exporter strategy instance.
     *
     * @param string $strategy
     * @return ModelExporterInterface
     */
    public function make($strategy);

}
