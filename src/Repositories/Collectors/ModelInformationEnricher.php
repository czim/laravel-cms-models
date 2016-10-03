<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\EnricherStepInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Repositories\Collectors\Enricher;
use Czim\CmsModels\Support\Data\ModelInformation;

class ModelInformationEnricher implements ModelInformationEnricherInterface
{

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $info;

    /**
     * Enrichment step FQNs.
     *
     * @var string[]
     */
    protected $steps = [
        Enricher\EnrichBasicListData::class,
        Enricher\EnrichListColumnData::class,
        Enricher\EnrichListFilterData::class,
    ];

    /**
     * @param ModelInformationInterface|ModelInformation $information
     * @return ModelInformationInterface|ModelInformation
     */
    public function enrich(ModelInformationInterface $information)
    {
        $this->info = $information;

        foreach ($this->steps as $step) {
            /** @var EnricherStepInterface $instance */
            $instance = app($step);

            $this->info = $instance->enrich($this->info);
        }

        return $information;
    }

}
