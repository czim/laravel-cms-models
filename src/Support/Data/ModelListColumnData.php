<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelListColumnDataInterface;

/**
 * Class ModelListColumnInformation
 *
 * Data container that represents a column presence in an index/listing for a model.
 *
 * @property bool $hide
 * @property string $source
 * @property string|array $strategy
 * @property string $label
 * @property string $label_translated
 * @property string $style
 * @property bool $editable
 * @property bool $sortable
 * @property string $sort_strategy
 * @property string $sort_direction asc|desc
 * @property array $options
 * @property bool $disable_default_action
 */
class ModelListColumnData extends AbstractDataObject implements ModelListColumnDataInterface
{

    protected $attributes = [

        // Whether to hide the list column.
        'hide' => false,

        // The source column or strategy to use. This may be a column on the model, or on models related to it.
        'source' => null,

        // Display strategy <FQN> or alias for displaying the source in the list.
        'strategy' => null,

        // Column header or label (or translation key) to show.
        'label' => null,
        'label_translated' => null,

        // Display style 'key' (css class, or whatever the front-end expects) that sets the rendering of the column value.
        // Suggestion: 'small', 'price, 'center', etc
        'style' => null,

        // Whether this column is supported for in-line editing.
        'editable' => false,

        // Whether it is possible to sort the list for this column
        'sortable' => false,

        // The sort strategy (class/FQN) to use for sorting
        'sort_strategy' => null,

        // Default sort direction for this column, if sortable
        'sort_direction' => 'asc',

        // Extra options for strategy configuration
        'options' => [],

        // Whether to disable the default row click action for this table cell.
        'disable_default_action' => null,
    ];

    /**
     * Returns display header label for the column.
     *
     * @return string
     */
    public function header()
    {
        if ($this->label_translated) {
            return cms_trans($this->label_translated);
        }

        if ($this->label) {
            return $this->label;
        }

        return ucfirst(str_replace('_', ' ', snake_case($this->source)));
    }

    /**
     * Returns associative array with custom options for strategies.
     *
     * @return array
     */
    public function options()
    {
        return $this->options ?: [];
    }

    /**
     * @param ModelListColumnDataInterface|ModelListColumnData $with
     */
    public function merge(ModelListColumnDataInterface $with)
    {
        $normalMerge = array_diff($this->getKeys(), ['options']);

        foreach ($normalMerge as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        $this->options = array_merge($this->options(), $with->options());
    }

}
