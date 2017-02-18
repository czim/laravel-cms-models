<?php
namespace Czim\CmsModels\Exceptions;

class ModelInformationEnrichmentException extends \Exception
{

    /**
     * FQN of the model being enriched.
     *
     * @var string|null
     */
    protected $modelClass;

    /**
     * Dot-notation representation of section, if known.
     *
     * @var string|null
     */
    protected $section;

    /**
     * Key for the object causing the problem, if known.
     *
     * @var string|null
     */
    protected $key;


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
     * @param string|null $section
     * @return $this
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @param string|null $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

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
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }

}
