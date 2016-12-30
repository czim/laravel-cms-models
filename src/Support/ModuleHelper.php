<?php
namespace Czim\CmsModels\Support;

use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Illuminate\Database\Eloquent\Model;

class ModuleHelper implements ModuleHelperInterface
{
    const MODULE_PREFIX = 'models.';

    /**
     * Returns the module key that corresponds to a given model FQN.
     *
     * This includes the 'models.' prefix.
     *
     * @param string|Model $model
     * @return string
     */
    public function moduleKeyForModel($model)
    {
        return static::MODULE_PREFIX . $this->modelSlug($model);
    }

    /**
     * Returns the model slug for a model or model FQN.
     *
     * @param string|Model $model
     * @return string
     */
    public function modelSlug($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        return str_replace('\\', '-', strtolower($model));
    }

    /**
     * Returns the model information key that corresponds to a given model FQN.
     *
     * @param string|Model $model
     * @return string
     */
    public function modelInformationKeyForModel($model)
    {
        return $this->modelSlug($model);
    }

}
