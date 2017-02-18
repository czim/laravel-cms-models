<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Analyzer\ModelAnalyzer;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationCollectorInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationInterpreterInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Exceptions\ModelInformationCollectionException;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\SplFileInfo;
use UnexpectedValueException;

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
     * List of model classes included for the CMS.
     *
     * @var string[]
     */
    protected $modelClasses = [];

    /**
     * List of CMS model information files.
     *
     * @var SplFileInfo[]
     */
    protected $cmsModelFiles = [];

    /**
     * @var ModelInformationEnricherInterface
     */
    protected $informationEnricher;

    /**
     * @var ModelInformationInterpreterInterface
     */
    protected $informationInterpreter;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;


    /**
     * @param ModuleHelperInterface                $moduleHelper
     * @param ModelAnalyzer                        $modelAnalyzer
     * @param ModelInformationEnricherInterface    $informationEnricher
     * @param ModelInformationInterpreterInterface $informationInterpreter
     * @param Filesystem                           $files
     */
    public function __construct(
        ModuleHelperInterface $moduleHelper,
        ModelAnalyzer $modelAnalyzer,
        ModelInformationEnricherInterface $informationEnricher,
        ModelInformationInterpreterInterface $informationInterpreter,
        Filesystem $files
    ) {
        $this->moduleHelper           = $moduleHelper;
        $this->modelAnalyzer          = $modelAnalyzer;
        $this->informationEnricher    = $informationEnricher;
        $this->informationInterpreter = $informationInterpreter;
        $this->files                  = $files;
    }


    /**
     * Collects and returns information about models.
     *
     * @return Collection|ModelInformationInterface[]
     */
    public function collect()
    {
        $this->information = new Collection;

        $this->cmsModelFiles = $this->getCmsModelFiles();
        $this->modelClasses  = $this->getModelsToCollect();

        $this->collectRawModels()
             ->collectCmsModels()
             ->enrichModelInformation();

        return $this->information;
    }

    /**
     * Collects information about config-defined app model classes.
     *
     * @return $this
     * @throws ModelInformationCollectionException
     */
    protected function collectRawModels()
    {
        foreach ($this->modelClasses as $class) {

            $key = $this->moduleHelper->modelInformationKeyForModel($class);

            try {
                $this->information->put($key, $this->modelAnalyzer->analyze($class));

            } catch (\Exception $e) {

                // Wrap and decorate exceptions so it is easier to track the problem source
                throw (new ModelInformationCollectionException(
                    "Issue analyzing model {$class}: \n{$e->getMessage()}",
                    $e->getCode(),
                    $e
                ))
                    ->setModelClass($class);
            }
        }

        return $this;
    }

    /**
     * Collects information from dedicated CMS model information classes.
     *
     * @return $this
     * @throws ModelInformationCollectionException
     */
    protected function collectCmsModels()
    {
        foreach ($this->cmsModelFiles as $file) {

            try {
                $this->collectSingleCmsModelFromFile($file);

            } catch (\Exception $e) {

                // Wrap and decorate exceptions so it is easier to track the problem source
                throw (new ModelInformationCollectionException(
                    "Issue reading/interpreting model configuration file {$file->getRealPath()}: \n{$e->getMessage()}",
                    $e->getCode(),
                    $e
                ))
                    ->setConfigurationFile($file->getRealPath());
            }
        }

        return $this;
    }

    /**
     * Collects CMS configuration information from a given Spl file.
     *
     * @param SplFileInfo $file
     */
    protected function collectSingleCmsModelFromFile(SplFileInfo $file)
    {
        $info = require $file->getRealPath();

        if ( ! is_array($info)) {
            throw new UnexpectedValueException(
                "Incorrect data from CMS model information file: '{$file->getRelativePath()}'"
            );
        }

        $info = $this->informationInterpreter->interpret($info);

        $modelClass = $this->makeModelFqnFromCmsModelPath(
            $file->getRelativePathname()
        );

        $key = $this->moduleHelper->modelInformationKeyForModel($modelClass);

        if ( ! $this->information->has($key)) {
            $this->getCore()->log('debug', "CMS model data for unset model information key '{$key}'");
            return;
        }

        /** @var ModelInformationInterface $originalInfo */
        $originalInfo = $this->information->get($key);
        $originalInfo->merge($info);

        $this->information->put($key, $originalInfo);
    }

    /**
     * Enriches collected model information, extrapolating from available data.
     *
     * @return $this
     */
    protected function enrichModelInformation()
    {
        $this->information = $this->informationEnricher->enrichMany($this->information);

        return $this;
    }

    /**
     * Returns a list of model FQNs for which to collect information.
     *
     * @return string[]
     */
    protected function getModelsToCollect()
    {
        $configDefined = config('cms-models.models', []);
        $cmsModels     = $this->getCmsModelClasses();

        return array_unique(
            array_merge($configDefined, $cmsModels)
        );
    }

    /**
     * Returns a list of CMS model class FQNs.
     *
     * @return string[]
     */
    protected function getCmsModelClasses()
    {
        if ( ! count($this->cmsModelFiles)) {
            return [];
        }

        $classes = [];

        foreach ($this->cmsModelFiles as $file) {

            $modelClass = $this->makeModelFqnFromCmsModelPath(
                $file->getRelativePathname()
            );

            if ( ! class_exists($modelClass)) continue;

            $classes[] = $modelClass;
        }

        return $classes;
    }

    /**
     * @return SplFileInfo[]
     */
    protected function getCmsModelFiles()
    {
        $cmsModelsDir = config('cms-models.collector.source.dir');

        if ( ! $cmsModelsDir || ! $this->files->isDirectory($cmsModelsDir)) {
            return [];
        }

        return $this->files->allFiles($cmsModelsDir);
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

    /**
     * Returns the FQN for the model that is related to a given cms model
     * information file path.
     *
     * @param string $path  relative path
     * @return string
     */
    protected function makeModelFqnFromCmsModelPath($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension) {
            $path = substr($path, 0, -1 * strlen($extension) - 1);
        }

        return rtrim(config('cms-models.collector.source.models-namespace'), '\\')
             . '\\' . str_replace('/', '\\', $path);
    }

    /**
     * @return CoreInterface
     */
    protected function getCore()
    {
        return app(Component::CORE);
    }

}
