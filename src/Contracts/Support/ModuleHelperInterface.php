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
     * Returns the model slug for a model or model FQN.
     *
     * @param string|Model $model
     * @return string
     */
    public function modelSlug($model);

    /**
     * Returns the model information key that corresponds to a given model FQN.
     *
     * @param string|Model $model
     * @return string
     */
    public function modelInformationKeyForModel($model);

}
