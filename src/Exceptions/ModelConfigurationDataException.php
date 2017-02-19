<?php
namespace Czim\CmsModels\Exceptions;

class ModelConfigurationDataException extends \Exception
{

    /**
     * The model configuration nested data key
     *
     * @var string
     */
    protected $dotKey;

    /**
     * @param string $dotKey
     * @return $this
     */
    public function setDotKey($dotKey)
    {
        $this->dotKey = $dotKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getDotKey()
    {
        return $this->dotKey;
    }

}
