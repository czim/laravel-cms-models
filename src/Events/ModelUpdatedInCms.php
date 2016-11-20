<?php
namespace Czim\CmsModels\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelUpdatedInCms
 *
 * Whenever a model was updated using the CMS.
 */
class ModelUpdatedInCms
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
