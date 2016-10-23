<?php
namespace Czim\CmsModels\Support\Data;

/**
 * Class ModelFormFieldGroupData
 *
 * Data container for layout of an (in-row) group of editable fields on a model's edit form.
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property array|string[] $children
 */
class ModelFormFieldGroupData extends AbstractModelFormLayoutNodeData
{

    protected $attributes = [

        'type' => 'group',

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Nested layout (field keys only at this level)
        'children' => [],
    ];

}
