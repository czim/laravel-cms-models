<?php
namespace Czim\CmsModels\Contracts\Support;

use Illuminate\Database\Eloquent\Model;

interface ModuleHelperInterface
{

    /**
     * Returns the module key that corresponds to a given model FQN.
     *
     * @param string|Model $model
     * @return string
     */
    public function moduleKeyForModel($model);

    /**
     * Returns the model information key that corresponds to a given model FQN.
     *
     * @param string|Model $model
     * @return mixed
     */
    public function modelInformationKeyForModel($model);

}
