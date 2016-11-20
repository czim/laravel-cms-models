<?php
namespace Czim\CmsModels\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelPositionUpdatedInCms
 *
 * Whenever a listify/positionable model had its position updated.
 */
class ModelPositionUpdatedInCms
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
