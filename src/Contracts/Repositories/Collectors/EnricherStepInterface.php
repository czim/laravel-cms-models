<?php
namespace Czim\CmsModels\Contracts\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
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

    /**
     * Sets parent enricher model.
     *
     * @param ModelInformationEnricherInterface $enricher
     * @return $this
     */
    public function setEnricher(ModelInformationEnricherInterface $enricher);

}
