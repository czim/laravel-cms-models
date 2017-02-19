<?php
namespace Czim\CmsModels\View\ListStrategies;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DefaultStrategy
 *
 * This is the fall-back default strategy used when no custom
 * strategy or alias has been defined.
 */
class DefaultStrategy extends AbstractListDisplayStrategy
{

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        return e($this->resolveModelSource($model, $source));
    }


}
