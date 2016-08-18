<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;

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
 */
class ModelListColumnData extends AbstractDataObject
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
    ];

}
