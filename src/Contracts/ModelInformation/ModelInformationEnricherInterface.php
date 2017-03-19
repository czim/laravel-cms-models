<?php
namespace Czim\CmsModels\Contracts\ModelInformation;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Support\Collection;

interface ModelInformationEnricherInterface
{

    /**
     * Sets a collection of information to use as context.
     *
     * @param Collection|ModelInformationInterface[] $information
     * @return $this
     */
    public function setAllModelInformation($information);

    /**
     * Returns collection of context model information, if set.
     *
     * @return Collection|ModelInformationInterface[]|ModelInformation[]|null
     */
    public function getAllModelInformation();

    /**
     * Enriches a collection of information.
     *
     * @param Collection|ModelInformationInterface[] $information
     * @return Collection|ModelInformationInterface[]
     */
    public function enrichMany($information);

    /**
     * Enriches a single model's information.
     *
     * Note that this does not offer contextual information for other models.
     *
     * @param ModelInformationInterface $information
     * @return ModelInformationInterface
     */
    public function enrich(ModelInformationInterface $information);

}
