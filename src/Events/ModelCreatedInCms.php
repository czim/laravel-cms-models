<?php
namespace Czim\CmsModels\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelCreatedInCms
 *
 * Whenever a model was created using the CMS.
 */
class ModelCreatedInCms
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
