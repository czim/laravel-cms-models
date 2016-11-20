<?php
namespace Czim\CmsModels\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelActivatedInCms
 *
 * Whenever a model was activated using the CMS.
 */
class ModelActivatedInCms
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
