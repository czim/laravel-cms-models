<?php
namespace Czim\CmsModels\View\ListStrategies;

use Czim\CmsModels\Contracts\View\ListDisplayInterface;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractListDisplayStrategy implements ListDisplayInterface
{

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string
     */
    abstract public function render(Model $model, $source);

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string|null
     */
    public function style(Model $model, $source)
    {
        return null;
    }

    /**
     * Returns an optional set of attribute values to merge into the list display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return array            associative, key value pairs with html tag attributes
     */
    public function attributes(Model $model, $source)
    {
        return [];
    }

}
