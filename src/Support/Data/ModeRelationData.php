<?php
namespace Czim\CmsModels\Support\Data;

use Czim\DataObject\AbstractDataObject;

/**
 * Class ModelRelationData
 *
 * Information about a model's relation (method).
 */
class ModelRelationData extends AbstractDataObject
{

    protected $attributes = [

        // Relation method name
        'method' => '',

        // Relation class name (HasMany, BelongsTo, etc)
        'type' => '',

        // Related model class name
        'class' => '',

    ];

}
