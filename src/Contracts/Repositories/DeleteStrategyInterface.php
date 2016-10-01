<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface DeleteStrategyInterface
{

    /**
     * Deletes a model.
     *
     * @param Model $model
     * @return mixed
     */
    public function delete(Model $model);

}
