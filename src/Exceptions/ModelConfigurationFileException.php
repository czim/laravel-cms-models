<?php
namespace Czim\CmsModels\Exceptions;

class ModelConfigurationFileException extends \Exception
{

    /**
     * The file that could not be interpreted
     *
     * @var string
     */
    protected $path;

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

}
