<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Support\Enums\LayoutNodeType;

/**
 * Class ModelFormFieldLabelData
 *
 * Data container for label in layout. Useful for field group, to add
 * in-row labels.
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property bool   $required
 */
class ModelFormFieldLabelData extends AbstractModelFormLayoutNodeData
{

    protected $attributes = [

        'type' => LayoutNodeType::LABEL,

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Whether the fields belonging to this are required (affects display only)
        'required' => null,

        // This layout type should not have children
        'children' => [],
    ];

}
