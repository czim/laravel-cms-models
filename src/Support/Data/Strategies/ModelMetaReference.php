<?php
namespace Czim\CmsModels\Support\Data\Strategies;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class ModelMetaReference
 *
 * @property string $model              the model to reference/search
 * @property string $strategy           the reference strategy to use
 * @property string $context_strategy   an optional extra context strategy to apply to the query builder
 * @property string $source             the source to display for a reference
 * @property string $target             the target(s) to use for any reference search
 * @property array  $parameters         optional parameters for the strategies
 */
class ModelMetaReference extends AbstractDataObject
{

    protected $attributes = [
        'model'            => null,
        'strategy'         => null,
        'context_strategy' => null,
        'source'           => null,
        'target'           => null,
        'parameters'       => [],
    ];

}
