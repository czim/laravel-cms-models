<?php
namespace Czim\CmsModels\Contracts\View;

use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\Data\ModelListColumnDataInterface;
use Illuminate\Database\Eloquent\Model;

interface ListDisplayInterface
{

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model  $model
     * @param mixed  $source    source value, relation instance, etc.
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

    /**
     * Sets the list column data to use.
     *
     * @param ModelListColumnDataInterface $data
     * @return $this
     */
    public function setListInformation(ModelListColumnDataInterface $data);

    /**
     * Sets the attribute column data to use.
     *
     * @param ModelAttributeDataInterface $data
     * @return $this
     */
    public function setAttributeInformation(ModelAttributeDataInterface $data);

}
