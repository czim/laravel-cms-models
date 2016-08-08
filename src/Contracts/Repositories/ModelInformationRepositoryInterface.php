<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ModelInformationRepositoryInterface
{

    /**
     * Initializes the repository so it may provide model information.
     *
     * @return $this
     */
    public function initialize();

    /**
     * Clears the cached model information.
     *
     * @return $this
     */
    public function clearCache();

    /**
     * Returns all sets of model information.
     *
     * @return Collection|ModelInformation[]
     */
    public function getAll();

    /**
     * Returns model information by key.
     *
     * @param string $key
     * @return ModelInformation|false
     */
    public function getByKey($key);

    /**
     * Returns model information by the model's FQN.
     *
     * @param string $class
     * @return ModelInformation|false
     */
    public function getByModelClass($class);

    /**
     * Returns model information by model instance.
     *
     * @param Model $model
     * @return ModelInformation|false
     */
    public function getByModel(Model $model);

}
