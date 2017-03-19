<?php
namespace Czim\CmsModels\Repositories;

use Czim\CmsModels\Contracts\Repositories\CurrentModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;

class CurrentModelInformation implements CurrentModelInformationInterface
{

    /**
     * @var ModelInformationRepositoryInterface
     */
    protected $repository;

    /**
     * @var RouteHelperInterface
     */
    protected $router;

    /**
     * The FQN of the model for the current route, if any
     *
     * @var string|null
     */
    protected $currentModelClass;

    /**
     * The FQN of the model for which this instance is active.
     *
     * @var string|null
     */
    protected $activeModelClass;


    /**
     * @param ModelInformationRepositoryInterface $repository
     * @param RouteHelperInterface                $router
     */
    public function __construct(
        ModelInformationRepositoryInterface $repository,
        RouteHelperInterface $router
    ) {
        $this->repository = $repository;
        $this->router     = $router;

        $this->setCurrentModelClass();
    }

    /**
     * Sets the active and current model class based on the current request.
     */
    protected function setCurrentModelClass()
    {
        if ( ! $this->router->isModelRoute()) {
            $this->currentModelClass = null;
            return $this;
        }

        $info = $this->repository->getByKey(
            $this->router->getModuleKeyForCurrentRoute()
        );

        if ($info) {
            $this->currentModelClass = $info->original_model ?: $info->model;
        } else {
            $this->currentModelClass = null;
        }

        $this->activeModelClass = $this->currentModelClass;

        return $this;
    }

    /**
     * Changes the active model class for which further methods on this
     * instance will be performed.
     *
     * @param string $class
     * @return $this
     */
    public function model($class)
    {
        if (empty($class)) {
            $class = null;
        }

        $this->activeModelClass = $class;

        return $this;
    }

    /**
     * Returns model information data object for active model.
     *
     * @return ModelInformation|false
     */
    public function info()
    {
        if (empty($this->activeModelClass)) {
            $info = false;
        } else {
            $info = $this->repository->getByModelClass($this->activeModelClass);
        }

        // Reset the active model class for future calls
        $this->activeModelClass = $this->currentModelClass;

        return $info;
    }

    /**
     * Returns whether the current request is related to a model.
     *
     * @return bool
     */
    public function isForModel()
    {
        return null !== $this->currentModelClass;
    }

    /**
     * Returns model FQN for current request.
     *
     * @return null|string
     */
    public function forModel()
    {
        return $this->currentModelClass;
    }

}
