<?php
namespace Czim\CmsModels\Support\Data;

use Czim\DataObject\AbstractDataObject;

/**
 * Class ModelAttributeData
 *
 * Information about a model's attribute.
 */
class ModelAttributeData extends AbstractDataObject
{

    protected $attributes = [

        // Attribute name
        'name' => '',

        // Cast type of attribute
        'cast' => 'string',

        // Database type of attribute
        'type' => 'varchar',

        // Whether the field is fillable
        'fillable' => false,

        // Whether the attribute is hidden
        'hidden' => false,

        // Whether the attribute is translated
        'translated' => false,

        // Maximum length of text field
        'length' => 255,

        // Whether the field is nullable
        'nullable' => true,

        // If numeric, whether the field is unsigned
        'unsigned' => false,

        // If enum, the available values
        'values' => [],

    ];

}
