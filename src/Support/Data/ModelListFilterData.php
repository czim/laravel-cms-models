<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class ModelListFilterInformation
 *
 * Data container that represents a filter presence/strategy in an index/listing for a model.
 *
 * @property string $source
 * @property string $target
 * @property string $strategy
 * @property array  $values
 */
class ModelListFilterData extends AbstractDataObject
{

    protected $attributes = [

        // If any known, the source that the filter is made for (attribute or relationship)
        'source' => null,

        // The target column, relation, or other strategy to filter against
        'target' => '',

        // The filter strategy to apply for rendering & application
        'strategy' => null,

        // Values for strategies that require a list of values
        'values' => [],

    ];

}
