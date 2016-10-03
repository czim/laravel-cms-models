<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Model;

interface ListStrategyInterface
{

    /**
     * Applies a strategy to render a list value from its source.
     *
     * @param Model             $model
     * @param string|array      $strategy
     * @param string|array|null $source
     * @return string
     */
    public function render(Model $model, $strategy, $source);

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source source column, method name or value
     * @return null|string
     */
    public function style(Model $model, $strategy, $source);

    /**
     * Returns an optional set of attribute values to merge into the list display value container.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source source column, method name or value
     * @return array associative, key value pairs with html tag attributes
     */
    public function attributes(Model $model, $strategy, $source);

}
