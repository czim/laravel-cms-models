<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Support\Enums\LayoutNodeType;

/**
 * Class ModelFormFieldGroupData
 *
 * Data container for layout of an (in-row) group of editable fields on a model's edit form.
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property bool   $required
 * @property array|string[] $children
 */
class ModelFormFieldGroupData extends AbstractModelFormLayoutNodeData
{

    protected $attributes = [

        'type' => LayoutNodeType::GROUP,

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Whether the fields belonging to this are required (affects display only)
        'required' => null,

        // Nested layout (field keys only at this level)
        'children' => [],
    ];

}
