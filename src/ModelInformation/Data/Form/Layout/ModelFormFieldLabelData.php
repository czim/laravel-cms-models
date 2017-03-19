<?php
namespace Czim\CmsModels\ModelInformation\Data\Form\Layout;

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
 * @property string $label_for
 * @property bool   $required
 */
class ModelFormFieldLabelData extends AbstractModelFormLayoutNodeData
{

    protected $attributes = [

        'type' => LayoutNodeType::LABEL,

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // The ID of the field label
        'label_for' => null,

        // Whether the fields belonging to this are required (affects display only)
        'required' => null,

        // This layout type should not have children
        'children' => [],
    ];

    protected $known = [
        'type',
        'label',
        'label_translated',
        'label_for',
        'required',
        'children',
    ];

}
