<?php
namespace Czim\CmsModels\Repositories;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationCollectorInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelInformationRepository implements ModelInformationRepositoryInterface
{

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
        $this->getFileSystem()->delete($this->getCachePath());

        return $this;
    }

    /**
     * Caches model information.
     *
     * @return $this
     */
    public function writeCache()
    {
        $this->getFileSystem()->put($this->getCachePath(), $this->serializedInformationForCache($this->information));

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
        return $this->getFileSystem()->exists($this->getCachePath());
    }

    /**
     * Returns cached model information.
     *
     * @return Collection|ModelInformation[]
     */
    protected function retrieveInformationFromCache()
    {
        if ( ! $this->isInformationCached()) {
            throw new \BadMethodCallException("Model information was not cached");
        }

        return $this->deserializeInformationFromCache(
            require($this->getCachePath())
        );
    }

    /**
     * Returns the path to which the model information should be cached.
     *
     * @return string
     */
    protected function getCachePath()
    {
        return app()->bootstrapPath() . '/cache/cms_model_information.php';
    }

    /**
     * @param Collection $information
     * @return string
     */
    protected function serializedInformationForCache(Collection $information)
    {
        return '<?php return ' . var_export($information->toArray(), true) . ';' . PHP_EOL;
    }

    /**
     * @param array $data
     * @return Collection|ModelInformationInterface[]
     */
    protected function deserializeInformationFromCache(array $data)
    {
        return new Collection(
            array_map(
                function ($modelData) {
                    return new ModelInformation($modelData);
                },
                $data
            )
        );
    }

    /**
     * @return Filesystem
     */
    protected function getFileSystem()
    {
        return app('files');
    }

}
