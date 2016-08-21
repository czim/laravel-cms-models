<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Analyzer\ModelAnalyzer;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationCollectorInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Support\Collection;

class ModelInformationCollector implements ModelInformationCollectorInterface
{

    /**
     * @var ModuleHelperInterface
     */
    protected $moduleHelper;

    /**
     * @var ModelAnalyzer
     */
    protected $modelAnalyzer;

    /**
     * @var Collection|ModelInformationInterface[]|ModelInformation[]
     */
    protected $information;

    /**
     * @var string[]
     */
    protected $modelClasses;

    /**
     * @var ModelInformationEnricherInterface
     */
    protected $informationEnricher;


    /**
     * @param ModuleHelperInterface             $moduleHelper
     * @param ModelAnalyzer                     $modelAnalyzer
     * @param ModelInformationEnricherInterface $informationEnricher
     */
    public function __construct(
        ModuleHelperInterface $moduleHelper,
        ModelAnalyzer $modelAnalyzer,
        ModelInformationEnricherInterface $informationEnricher
    ) {
        $this->moduleHelper        = $moduleHelper;
        $this->modelAnalyzer       = $modelAnalyzer;
        $this->informationEnricher = $informationEnricher;
    }


    /**
     * Collects and returns information about models.
     *
     * @return Collection|ModelInformationInterface[]
     */
    public function collect()
    {
        $this->information = new Collection;

        $this->modelClasses = $this->getModelsToCollect();

        $this->collectRawModels()
             ->collectCmsModels()
             ->enrichModelInformation();

        return $this->information;
    }

    /**
     * Collects information about config-defined app model classes.
     *
     * @return $this
     */
    protected function collectRawModels()
    {
        foreach ($this->modelClasses as $class) {

            $key = $this->moduleHelper->moduleKeyForModel($class);

            $this->information->put($key, $this->modelAnalyzer->analyze($class));
        }

        return $this;
    }

    /**
     * Collects information from dedicated CMS model information classes.
     *
     * @return $this
     */
    protected function collectCmsModels()
    {
        // todo
        // read information from special CMS model sources

        return $this;
    }

    /**
     * Enriches collected model information, extrapolating from available data.
     *
     * @return $this
     */
    protected function enrichModelInformation()
    {
        foreach ($this->information as $key => $info) {

            $this->informationEnricher->enrich($info);
        }

        return $this;
    }


    /**
     * Returns a list of model FQNs for which to collect information.
     *
     * @return string[]
     */
    protected function getModelsToCollect()
    {
        return config('cms-models.models', []);
    }

    /**
     * Returns module key for a given model FQN.
     *
     * @param string $class
     * @return string
     */
    protected function getModuleKeyForModelClass($class)
    {
        return app('cms-models-modelinfo')->moduleKey($class);
    }

}
