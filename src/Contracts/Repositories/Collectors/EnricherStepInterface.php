<?php
namespace Czim\CmsModels\Contracts\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelInformation;

interface EnricherStepInterface
{

    /**
     * Performs enrichment on model information.
     *
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelInformationInterface|ModelInformation
     */
    public function enrich(ModelInformationInterface $info);

}
