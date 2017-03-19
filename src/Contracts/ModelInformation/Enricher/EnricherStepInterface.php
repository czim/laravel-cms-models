<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Enricher;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Support\Collection;

interface EnricherStepInterface
{

    /**
     * Performs enrichment on model information.
     *
     * Optionally takes all model information known as context.
     *
     * @param ModelInformationInterface|ModelInformation                     $info
     * @param Collection|ModelInformationInterface[]|ModelInformation[]|null $allInformation
     * @return ModelInformationInterface|ModelInformation
     */
    public function enrich(ModelInformationInterface $info, $allInformation = null);

}
