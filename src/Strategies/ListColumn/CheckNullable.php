<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Illuminate\Database\Eloquent\Model;

class CheckNullable extends Check
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
        if (null === $this->resolveModelSource($model, $source)) {
            return '';
        }

        return parent::render($model, $source);
    }


}
