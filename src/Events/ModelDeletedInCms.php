<?php
namespace Czim\CmsModels\Events;

/**
 * Class ModelDeletedInCms
 *
 * Whenever a model was deleted using the CMS, after it is deleted.
 */
class ModelDeletedInCms
{

    /**
     * @var string
     */
    public $class;

    /**
     * @var mixed
     */
    public $key;

    /**
     * @param string $class     model FQN
     * @param mixed  $key       key of the deleted model
     */
    public function __construct($class, $key)
    {
        $this->class = $class;
        $this->key   = $key;
    }

}
