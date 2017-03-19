<?php
namespace Czim\CmsModels\Strategies\Export\Column;

use Illuminate\Database\Eloquent\Model;

class BooleanStringStrategy extends DefaultStrategy
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
        $value = $this->resolveModelSource($model, $source);

        if (null === $value) {
            return null;
        }

        if ($value) {
            return cms_trans('common.boolean.true');
        }

        return cms_trans('common.boolean.false');
    }

}
