<?php
namespace Czim\CmsModels\Contracts\Strategies;

use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

interface ShowFieldInterface
{

    /**
     * Renders a display value to print to the view.
     *
     * @param Model  $model
     * @param mixed  $source    source value, relation instance, etc.
     * @return string|View
     */
    public function render(Model $model, $source);

    /**
     * Returns an optional style string for the display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string|null
     */
    public function style(Model $model, $source);

    /**
     * Returns an optional set of attribute values to merge into the display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return array            associative, key value pairs with html tag attributes
     */
    public function attributes(Model $model, $source);

    /**
     * Returns custom options for the strategy.
     *
     * @return array
     */
    public function options();

    /**
     * Sets custom options.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Sets the attribute column data to use.
     *
     * @param ModelAttributeDataInterface $data
     * @return $this
     */
    public function setAttributeInformation(ModelAttributeDataInterface $data);

}
