<?php
namespace Czim\CmsModels\Support\Data;

use Czim\DataObject\AbstractDataObject;

/**
 * Class ModelAttributeData
 *
 * Information about a model's attribute.
 *
 * @property string $name
 * @property string $cast
 * @property string $type
 * @property string $strategy
 * @property string $strategy_form
 * @property string $strategy_list
 * @property bool $fillable
 * @property bool $hidden
 * @property bool $translated
 * @property int $length
 * @property bool $nullable
 * @property bool $unsigned
 * @property array $values
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

        // General strategy for treating or displaying attribute
        'strategy' => '',
        // Strategy for displaying form field for this attribute
        'strategy_form' => '',
        // Strategy for displaying attribute in list/index
        'strategy_list' => '',

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
