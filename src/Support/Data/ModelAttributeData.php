<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Support\Enums\AttributeCast;

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
class ModelAttributeData extends AbstractDataObject implements ModelAttributeDataInterface
{

    protected $attributes = [

        // Attribute name
        'name' => '',

        // Cast type of attribute
        'cast' => '',

        // Database type of attribute
        'type' => '',

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


    /**
     * @param ModelAttributeDataInterface|ModelAttributeData $data
     */
    public function merge(ModelAttributeDataInterface $data)
    {
        $mergeEmptyAttributes = [
            'name',
            'cast',
            'type',
            'strategy',
            'strategy_form',
            'strategy_list',
        ];

        foreach ($mergeEmptyAttributes as $key) {
            if ( ! empty($this[$key])) continue;

            $this->mergeAttribute($key, $data[$key]);
        }
    }

    /**
     * Merges data for an attribute's translated column.
     *
     * @param ModelAttributeDataInterface|ModelAttributeData $data
     */
    public function mergeTranslation(ModelAttributeDataInterface $data)
    {
        $this->merge($data);
    }

    /**
     * @return bool
     */
    public function isNumeric()
    {
        return in_array($this->cast, [
            AttributeCast::INTEGER,
            AttributeCast::FLOAT,
        ] );
    }

}
