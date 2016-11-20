<?php
namespace Czim\CmsModels\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelDeactivatedInCms
 *
 * Whenever a model was de-activated using the CMS.
 */
class ModelDeactivatedInCms
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
