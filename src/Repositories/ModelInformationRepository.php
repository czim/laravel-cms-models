<?php
namespace Czim\CmsModels\Repositories;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationCollectorInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelInformationRepository implements ModelInformationRepositoryInterface
{
    const CACHE_KEY = 'models.information';

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var Collection|ModelInformation[]
     */
    protected $information;

    /**
     * Index to help find information by model class FQN.
     *
     * @var string[]
     */
    protected $modelClassIndex = [];

    /**
     * @var ModelInformationCollectorInterface
     */
    protected $collector;

    /**
     * Whether the repository has been initialized.
     *
     * @var bool
     */
    protected $initialized = false;


    /**
     * @param CoreInterface                      $core
     * @param ModelInformationCollectorInterface $collector
     */
    public function __construct(CoreInterface $core, ModelInformationCollectorInterface $collector)
    {
        $this->core      = $core;
        $this->collector = $collector;

        $this->information = new Collection;
    }


    /**
     * Initializes the repository so it may provide model information.
     *
     * @return $this
     */
    public function initialize()
    {
        if ($this->isInformationCached()) {

            $this->information = $this->retrieveInformationFromCache();

        } else {

            $this->information = $this->collector->collect();

            $this->storeInformationInCache();
        }

        $this->fillModelClassIndex();

        $this->initialized = true;

        return $this;
    }


    /**
     * Returns all sets of model information.
     *
     * @return Collection|ModelInformation[]
     */
    public function getAll()
    {
        $this->checkInitialization();

        return $this->information;
    }

    /**
     * Returns model information by key.
     *
     * @param string $key
     * @return ModelInformation|false
     */
    public function getByKey($key)
    {
        $this->checkInitialization();

        return $this->information->get($key) ?: false;
    }

    /**
     * Returns model information by the model's FQN.
     *
     * @param string $class
     * @return ModelInformation|false
     */
    public function getByModelClass($class)
    {
        $this->checkInitialization();

        if ( ! array_key_exists($class, $this->modelClassIndex)) {
            return false;
        }

        return $this->getByKey($this->modelClassIndex[$class]);
    }

    /**
     * Returns model information by model instance.
     *
     * @param Model $model
     * @return ModelInformation|false
     */
    public function getByModel(Model $model)
    {
        $this->checkInitialization();

        return $this->getByModelClass(get_class($model));
    }

    /**
     * Clears the cached model information.
     *
     * @return $this
     */
    public function clearCache()
    {
        $this->core->cache()->forget(static::CACHE_KEY);

        return $this;
    }


    /**
     * Checks whether the repository has been initialized.
     */
    protected function checkInitialization()
    {
        if ( ! $this->initialized) {
            $this->initialize();
        }
    }

    /**
     * Populates the index for associated class FQNs with the loaded information.
     *
     * @return $this
     */
    protected function fillModelClassIndex()
    {
        $this->modelClassIndex = [];

        foreach ($this->information as $index => $information) {
            $this->modelClassIndex[ $information->modelClass() ] = $index;
        }

        return $this;
    }

    // ------------------------------------------------------------------------------
    //      Cache
    // ------------------------------------------------------------------------------

    /**
     * Returns whether model information has been cached.
     *
     * @return bool
     */
    protected function isInformationCached()
    {
        return $this->isCacheEnabled() && $this->core->cache()->has(static::CACHE_KEY);
    }

    /**
     * Returns cached model information.
     *
     * @return Collection|ModelInformation[]
     */
    protected function retrieveInformationFromCache()
    {
        if ( ! $this->isCacheEnabled() || ! $this->isInformationCached()) {
            throw new \BadMethodCallException("Model information was not cached");
        }

        return $this->core->cache()->get(static::CACHE_KEY);
    }

    /**
     * Caches model information.
     *
     * @return $this
     */
    protected function storeInformationInCache()
    {
        if ($this->isCacheEnabled()) {
            $this->core->cache()->forever(static::CACHE_KEY, $this->information);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    protected function isCacheEnabled()
    {
        return config('cms-models.repository.cache', false);
    }

}
