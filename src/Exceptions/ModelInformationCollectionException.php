<?php
namespace Czim\CmsModels\Exceptions;

class ModelInformationCollectionException extends \Exception
{

    /**
     * FQN of the model being enriched.
     *
     * @var string|null
     */
    protected $modelClass;

    /**
     * Configuration filename being accessed.
     *
     * @var string|null
     */
    protected $configurationFile;


    /**
     * @param string|null $modelClass
     * @return $this
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    /**
     * @param string|null $file
     * @return $this
     */
    public function setConfigurationFile($file)
    {
        $this->configurationFile = $file;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @return string|null
     */
    public function getConfigurationFile()
    {
        return $this->configurationFile;
    }

}
