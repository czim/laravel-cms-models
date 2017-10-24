<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class HexColor extends AbstractListDisplayStrategy
{
    const VIEW = 'cms-models::model.partials.list.strategies.hex_color';

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        return view(static::VIEW, [
            'color' => $this->resolveModelSource($model, $source),
        ]);
    }

}
