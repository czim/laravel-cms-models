<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface OrderableStrategyInterface
{

    /**
     * Sets a new orderable position for a model.
     *
     * @param Model $model
     * @param mixed $position
     * @return mixed|false
     */
    public function setPosition(Model $model, $position);

}
