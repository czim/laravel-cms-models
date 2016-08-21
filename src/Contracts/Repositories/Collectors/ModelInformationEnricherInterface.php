<?php
namespace Czim\CmsModels\Contracts\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;

interface ModelInformationEnricherInterface
{

    /**
     * @param ModelInformationInterface $information
     * @return ModelInformationInterface
     */
    public function enrich(ModelInformationInterface $information);

}
