<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

interface CurrentModelInformationInterface
{

    /**
     * Changes the active model class for which further methods on this
     * instance will be performed.
     *
     * @param string $class
     * @return $this
     */
    public function model($class);

    /**
     * Returns model information data object for active model.
     *
     * @return ModelInformationInterface|false
     */
    public function info();

    /**
     * Returns whether the current request is related to a model.
     *
     * @return bool
     */
    public function isForModel();

    /**
     * Returns model FQN for current request.
     *
     * @return null|string
     */
    public function forModel();

}
