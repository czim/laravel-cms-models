<?php
namespace Czim\CmsModels\Modules;

use Czim\CmsCore\Contracts\Modules\ModuleGeneratorInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Support\Collection;

class ModelModuleGenerator implements ModuleGeneratorInterface
{

    /**
     * @var ModelInformationRepositoryInterface
     */
    protected $repository;

    /**
     * @var ModuleHelperInterface
     */
    protected $moduleHelper;


    /**
     * @param ModelInformationRepositoryInterface $repository
     * @param ModuleHelperInterface               $moduleHelper
     */
    public function __construct(ModelInformationRepositoryInterface $repository, ModuleHelperInterface $moduleHelper)
    {
        $this->repository   = $repository;
        $this->moduleHelper = $moduleHelper;
    }


    /**
     * Generates and returns module instances.
     *
     * @return Collection|ModuleInterface[]
     */
    public function modules()
    {
        $modules = new Collection;

        // Make meta module
        $modules->push(
            $this->makeMetaModuleInstance()
        );

        // Make model modules
        foreach ($this->repository->getAll() as $modelInformation) {
            $modules->push(
                $this->makeModuleInstance($modelInformation)
            );
        }

        return $modules;
    }


    /**
     * Makes the model meta module.
     *
     * @return ModelMetaModule
     */
    protected function makeMetaModuleInstance()
    {
        return new ModelMetaModule;
    }

    /**
     * Makes a model module instance for model information.
     *
     * @param ModelInformationInterface|ModelInformation $information
     * @return ModelModule
     */
    protected function makeModuleInstance(ModelInformationInterface $information)
    {
        $modelClass = $information->modelClass();

        $module = new ModelModule(
            app(ModelInformationRepositoryInterface::class),
            $this->moduleHelper,
            app(RouteHelperInterface::class),
            $this->makeModuleKey($modelClass),
            $this->makeModuleName($modelClass)
        );

        $module->setAssociatedClass($modelClass);

        if ($information->meta->controller) {
            $module->setWebController($information->meta->controller);
        }

        if ($information->meta->controller_api) {
            $module->setApiController($information->meta->controller_api);
        }

        return $module;
    }

    /**
     * @param string $modelClass
     * @return string
     */
    protected function makeModuleKey($modelClass)
    {
        return $this->moduleHelper->moduleKeyForModel($modelClass);
    }

    /**
     * @param string $modelClass
     * @return string
     */
    protected function makeModuleName($modelClass)
    {
        return 'Models: ' . $modelClass;
    }

}
