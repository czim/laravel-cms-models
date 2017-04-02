<?php
namespace Czim\CmsModels\ModelInformation\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelActionReferenceDataInterface;

/**
 * Class ModelActionReferenceData
 *
 * Information about a route action that may be performed, usually for a click.
 *
 * @property string               $strategy
 * @property string|string[]|null $permissions
 * @property array                $options
 */
class ModelActionReferenceData extends AbstractDataObject implements ModelActionReferenceDataInterface
{

    protected $attributes = [

        // An identifier for the strategy: alias or class.
        'strategy' => null,

        // The permission(s) required to use this action. May be a string or an array.
        // If more are given, all must be permitted.
        'permissions' => null,

        // Special options for custom types: key value pairs
        'options' => [],
    ];


    /**
     * Returns the strategy class or alias.
     *
     * @return string|null
     */
    public function strategy()
    {
        return $this->getAttribute('strategy');
    }

    /**
     * Returns required permissions for this action.
     *
     * @return string[]
     */
    public function permissions()
    {
        $permissions = $this->getAttribute('permissions') ?: [];

        if ( ! is_array($permissions)) {
            $permissions = [ $permissions ];
        }

        return $permissions;
    }

    /**
     * Returns custom options.
     *
     * @return array
     */
    public function options()
    {
        return $this->getAttribute('options') ?: [];
    }


    /**
     * @param ModelActionReferenceDataInterface|ModelActionReferenceData $with
     */
    public function merge(ModelActionReferenceDataInterface $with)
    {
        $mergeAttributes = [
            'strategy',
            'options',
            'permissions',
        ];

        foreach ($mergeAttributes as $attribute) {
            $this->mergeAttribute($attribute, $with->{$attribute});
        }
    }

}
