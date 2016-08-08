<?php
namespace Czim\CmsModels\Support\Data;

use Czim\DataObject\AbstractDataObject;

/**
 * Class ModelFormFieldData
 *
 * Data container that describes an editable field on a model's create/update form
 */
class ModelFormFieldData extends AbstractDataObject
{

    protected $attributes = [

        // The unique identifying key for this field's value(s)
        // On submit, this key should hold the form's field value.
        'key' => null,

        // Whether the field should be present on a create form
        'create' => true,
        // Whether the field should be present on an update form
        'update' => true,

        // Field label (or translation key) to show
        'label' => null,

        // Editing source/target strategy for the form field. Default is direct record on the model
        'source' => null,

        // Form field general type ('text', 'password', 'checkbox', etc)
        'type' => null,

        // Strategy for presenting the field
        'presenter' => null,

        // Whether the value being edited is translated (follows the translation_strategy defined @ top level)
        'translated' => false,

        // Display style 'key' (css class, or whatever the front-end expects) that sets the rendering of the field
        // Suggestion: 'small', 'price, 'center', etc
        'style' => null,

    ];

}
