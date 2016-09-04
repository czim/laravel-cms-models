<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelListColumnDataInterface;

/**
 * Class ModelListColumnInformation
 *
 * Data container that represents a column presence in an index/listing for a model.
 *
 * @property string $source
 * @property string|array $strategy
 * @property string $label
 * @property string $label_translated
 * @property string $style
 * @property bool $editable
 * @property bool $sortable
 * @property string $sort_strategy
 * @property string $sort_direction asc|desc
 */
class ModelListColumnData extends AbstractDataObject implements ModelListColumnDataInterface
{

    protected $attributes = [

        // The source column or strategy to use. This may be a column on the model, or on models related to it,
        // or a present() method on the model, if it has a presenter.
        'source' => null,

        // Strategy <FQN>@<method> for decorating the source in the list with
        'strategy' => null,

        // Column header or label (or translation key) to show
        'label' => null,
        'label_translated' => null,

        // Whether to allow sorting (any non-null value) and if so, what strategy to apply.
        'sort' => null,

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
    ];

    /**
     * @param ModelListColumnDataInterface|ModelListColumnData $with
     */
    public function merge(ModelListColumnDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
