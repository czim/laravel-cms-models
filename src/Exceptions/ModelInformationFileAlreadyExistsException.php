<?php
namespace Czim\CmsModels\Exceptions;

class ModelInformationFileAlreadyExistsException extends \Exception
{

    /**
     * FQN of the model having its information written.
     *
     * @var string|null
     */
    protected $modelClass;


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
     * @return string|null
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

}
