<?php
namespace Czim\CmsModels\ModelInformation\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class ModelRelationData
 *
 * Information about a model's relation (method).
 *
 * @property string $name
 * @property string $method
 * @property string $type
 * @property string $relationClass
 * @property string $relatedModel
 * @property string[] $morphModels
 * @property string $strategy
 * @property string $strategy_form
 * @property string $strategy_list
 * @property string[] $foreign_keys
 * @property bool $nullable_key
 * @property bool $translated
 */
class ModelRelationData extends AbstractDataObject
{

    protected $attributes = [

        // Relation name (key for form fields, for instance)
        'name' => '',

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

        // The foreign keys to the related model, if they are stored on this model
        // (for morph relations, the id/type, otherwise just the one key)
        'foreign_keys' => [],

        // Whether the foreign key of this relation is nullable
        'nullable_key' => null,

        // Whether the relation is translated (ie. whether it is a relation on the translation model)
        'translated' => null,
    ];

}
