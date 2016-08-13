<?php
namespace Czim\CmsModels\Support\Data;

use Czim\DataObject\AbstractDataObject;

/**
 * Class ModelRelationData
 *
 * Information about a model's relation (method).
 *
 * @property string $method
 * @property string $type
 * @property string $relationClass
 * @property string $relatedModel
 * @property string[] $morphModels
 * @property string $strategy
 * @property string $strategy_form
 * @property string $strategy_list
 */
class ModelRelationData extends AbstractDataObject
{

    protected $attributes = [

        // Relation method name
        'method' => '',

        // Relation type name (hasMany, belongsTo, etc)
        'type' => '',

        // Relation class FQN name (HasMany, BelongsTo, etc)
        'relationClass' => '',

        // Related model class name
        'relatedModel' => '',

        // Related model classnames  for polymorphic relations
        'morphModels' => [],

        // General strategy for treating or displaying attribute
        'strategy' => '',
        // Strategy for displaying form field for this attribute
        'strategy_form' => '',
        // Strategy for displaying attribute in list/index
        'strategy_list' => '',

    ];

}
