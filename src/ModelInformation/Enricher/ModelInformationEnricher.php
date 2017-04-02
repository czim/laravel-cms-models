<?php
namespace Czim\CmsModels\ModelInformation\Enricher;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\ModelInformation\Enricher\EnricherStepInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
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
        Steps\EnrichBasicListData::class,
        Steps\EnrichListColumnData::class,
        Steps\EnrichListFilterData::class,
        Steps\EnrichFormFieldData::class,
        Steps\EnrichFormLayoutData::class,
        Steps\EnrichShowFieldData::class,
        Steps\EnrichExportStrategyData::class,
        Steps\EnrichExportColumnData::class,
        Steps\EnrichValidationData::class,
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
        if (null === $this->allInfo) {
            return new Collection;
        }

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
     * @throws ModelInformationEnrichmentException
     */
    public function enrich(ModelInformationInterface $information)
    {
        $this->info = $information;

        try {
            foreach ($this->steps as $step) {
                /** @var EnricherStepInterface $instance */
                $instance = app($step, [ $this ]);

                $this->info = $instance->enrich($this->info);
            }

        } catch (\Exception $e) {

            $key     = null;
            $section = null;
            $message = $e->getMessage();

            if ($e instanceof ModelInformationEnrichmentException) {
                $key     = $e->getKey();
                $section = $e->getSection();
            } elseif ($e instanceof ModelConfigurationDataException) {
                $message .= " ({$e->getDotKey()})";
            }

            // Wrap and decorate exceptions so it is easier to track the problem source
            throw (new ModelInformationEnrichmentException(
                "{$information->modelClass()} model configuration issue: \n{$message}",
                $e->getCode(),
                $e
            ))
                ->setModelClass($information->modelClass())
                ->setSection($section)
                ->setKey($key);
        }

        return $information;
    }

}
