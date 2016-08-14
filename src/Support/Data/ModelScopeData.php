<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class ModelScopeData
 *
 * Information about a model's scope.
 *
 * @property string $method
 * @property string $label
 * @property string $strategy
 */
class ModelScopeData extends AbstractDataObject
{

    protected $attributes = [

        // Relation method name
        'method' => '',

        // Display label (or translation key)
        'label' => '',

        // General strategy for handling scope
        'strategy' => '',

    ];

}
