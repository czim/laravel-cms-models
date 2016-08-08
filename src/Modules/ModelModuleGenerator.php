<?php
namespace Czim\CmsModels\Modules;

use Czim\CmsCore\Contracts\Modules\ModuleGeneratorInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Illuminate\Support\Collection;

class ModelModuleGenerator implements ModuleGeneratorInterface
{

    /**
     * @var ModelInformationRepositoryInterface
     */
    protected $repository;


    /**
     * @param ModelInformationRepositoryInterface $repository
     */
    public function __construct(ModelInformationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Generates and returns module instances.
     *
     * @return Collection|ModuleInterface[]
     */
    public function modules()
    {
        $modules = new Collection;

        foreach ($this->repository->getAll() as $modelInformation) {
            $modules->push(
                $this->makeModuleInstance($modelInformation)
            );
        }

        return $modules;
    }


    /**
     * Makes a model module instance for model information.
     *
     * @param ModelInformationInterface $information
     * @return ModelModule
     */
    protected function makeModuleInstance(ModelInformationInterface $information)
    {
        $modelClass = $information->modelClass();

        $module = new ModelModule(
            app(ModelInformationRepositoryInterface::class),
            app(RouteHelperInterface::class),
            $this->makeModuleKey($modelClass),
            $this->makeModuleName($modelClass)
        );

        $module->setAssociatedClass($modelClass);

        return $module;
    }

    /**
     * @param string $modelClass
     * @return string
     */
    protected function makeModuleKey($modelClass)
    {
        return 'models.' . strtolower(str_replace('\\', '-', $modelClass));
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
