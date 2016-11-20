<?php
namespace Czim\CmsModels\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeletingModelInCms
 *
 * Whenever a model deletion was initiated in the CMS.
 * This is called during deletion, before the actual deletion happens.
 */
class DeletingModelInCms
{

    /**
     * @var Model
     */
    public $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

}
