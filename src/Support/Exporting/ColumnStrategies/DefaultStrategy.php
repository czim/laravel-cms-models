<?php
namespace Czim\CmsModels\Support\Exporting\ColumnStrategies;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DefaultStrategy
 *
 * This is the fall-back default strategy used when no custom
 * strategy or alias has been defined.
 */
class DefaultStrategy extends AbstractColumnStrategy
{

    /**
     * Renders a display value to print to the export.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        return $this->resolveModelSource($model, $source);
    }


}
