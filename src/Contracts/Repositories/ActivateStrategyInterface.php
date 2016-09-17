<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface ActivateStrategyInterface
{

    /**
     * Updates a model's active status.
     *
     * @param Model $model
     * @param bool  $activate
     * @return bool
     */
    public function update(Model $model, $activate = true);

}
