<?php
namespace Czim\CmsModels\Support;

use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Illuminate\Database\Eloquent\Model;

class ModuleHelper implements ModuleHelperInterface
{

    /**
     * Returns the module key that corresponds to a given model FQN.
     *
     * @param string|Model $model
     * @return string
     */
    public function moduleKeyForModel($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        return str_replace('\\', '-', strtolower($model));
    }

}
