<?php
namespace Czim\CmsModels\Contracts\Strategies;

use Illuminate\Database\Eloquent\Model;

interface DeleteStrategyInterface
{

    /**
     * Deletes a model.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model);

}
