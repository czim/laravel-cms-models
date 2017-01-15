<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\EnricherStepInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Repositories\Collectors\Enricher;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Support\Collection;

class ModelInformationEnricher implements ModelInformationEnricherInterface
{

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $info;

    /**
     * @var Collection|ModelInformationInterface[]|ModelInformation[]
     */
    protected $allInfo;

    /**
     * Enrichment step FQNs.
     *
     * @var string[]
     */
    protected $steps = [
        Enricher\EnrichBasicListData::class,
        Enricher\EnrichListColumnData::class,
        Enricher\EnrichListFilterData::class,
        Enricher\EnrichFormFieldData::class,
        Enricher\EnrichFormLayoutData::class,
        Enricher\EnrichShowFieldData::class,
        Enricher\EnrichExportColumnData::class,
        Enricher\EnrichValidationData::class,
    ];


    /**
     * Sets a collection of information to use as context.
     *
     * @param Collection|ModelInformationInterface[]|ModelInformation[] $information
     * @return $this
     */
    public function setAllModelInformation($information)
    {
        $this->allInfo = $information;

        return $this;
    }

    /**
     * Returns collection of context model information, if set.
     *
     * @return Collection|ModelInformationInterface[]|ModelInformation[]|null
     */
    public function getAllModelInformation()
    {
        return $this->allInfo;
    }

    /**
     * Enriches a collection of information.
     *
     * @param Collection|ModelInformationInterface[]|ModelInformation[] $information
     * @return Collection|ModelInformationInterface[]|ModelInformation[]
     */
    public function enrichMany($information)
    {
        $this->allInfo = $information;

        return $information->transform([ $this, 'enrich' ]);
    }

    /**
     * Enriches a single model's information.
     *
     * Note that this does not offer contextual information for other models.
     *
     * @param ModelInformationInterface|ModelInformation $information
     * @return ModelInformationInterface|ModelInformation
     */
    public function enrich(ModelInformationInterface $information)
    {
        $this->info = $information;

        foreach ($this->steps as $step) {
            /** @var EnricherStepInterface $instance */
            $instance = app($step, [ $this ]);

            $this->info = $instance->enrich($this->info);
        }

        return $information;
    }

}
