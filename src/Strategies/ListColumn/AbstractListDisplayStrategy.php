<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\Data\ModelListColumnDataInterface;
use Czim\CmsModels\Contracts\View\ListDisplayInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelListColumnData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesSourceStrategies;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractListDisplayStrategy implements ListDisplayInterface
{
    use ResolvesSourceStrategies;

    /**
     * @var ModelListColumnDataInterface|ModelListColumnData
     */
    protected $listColumnData;

    /**
     * @var ModelAttributeDataInterface|ModelAttributeData
     */
    protected $attributeData;

    /**
     * @var array
     */
    protected $options = [];


    /**
     * Renders a display value to print to the list view.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string|View
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
        if ( ! $this->attributeData) {
            return null;
        }

        switch ($this->attributeData->cast) {

            case AttributeCast::INTEGER:
            case AttributeCast::FLOAT:
                return 'column-right';

            case AttributeCast::DATE:
                return 'column-date';

            // default omitted on purpose
        }

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

    /**
     * Returns custom options.
     *
     * @return array
     */
    public function options()
    {
        if ($this->options && count($this->options)) {
            return $this->options;
        }

        if ( ! $this->listColumnData) {
            return [];
        }

        return $this->listColumnData->options();
    }

    /**
     * Sets custom options.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets the list column data to use.
     *
     * @param ModelListColumnDataInterface|ModelListColumnData $data
     * @return $this
     */
    public function setListInformation(ModelListColumnDataInterface $data)
    {
        $this->listColumnData = $data;

        return $this;
    }

    /**
     * Sets the attribute column data to use.
     *
     * @param ModelAttributeDataInterface|ModelAttributeData $data
     * @return $this
     */
    public function setAttributeInformation(ModelAttributeDataInterface $data)
    {
        $this->attributeData = $data;

        return $this;
    }

    /**
     * Initializes the strategy instance for further calls.
     *
     * Should be called after setListInformation, if this is set at all.
     *
     * @param string $modelClass
     * @return $this
     */
    public function initialize($modelClass)
    {
        return $this;
    }

}
