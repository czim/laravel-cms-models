<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Model;

interface ListDisplayInterface
{

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string
     */
    public function render(Model $model, $source);

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string|null
     */
    public function style(Model $model, $source);

    /**
     * Returns an optional set of attribute values to merge into the list display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return array            associative, key value pairs with html tag attributes
     */
    public function attributes(Model $model, $source);

}
