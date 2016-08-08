<?php
namespace Czim\CmsModels\Support\Data;

use Czim\DataObject\AbstractDataObject;

/**
 * Class ModelFormFieldData
 *
 * Data container that describes an (in-row) group of editable fields on a model's edit form.
 * This
 */
class ModelFormFieldGroupData extends AbstractDataObject
{

    protected $attributes = [

        // Whether this is a group for fields within the same form row.
        // If false, forms a normal multi-row form group
        'in-row' => true,

        // Whether the field should be present on a create form
        'create' => true,
        // Whether the field should be present on an update form
        'update' => true,

        // Field label (or translation key) to show
        'label' => null,

        // Editing strategy for the form field. Default is direct record on the model
        'type' => null,

        // Strategy for presenting the group
        'presenter' => null,

        // Arrays (instances of ModelFormFieldData) that define the editable fields for the model's form
        // in the order in which they should appear.
        'fields' => [],
    ];

}
