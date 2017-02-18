<?php
namespace Czim\CmsModels\Exceptions;

class ModelAnalysisException extends \Exception
{

    /**
     * FQN of the model being enriched.
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
