<?php
namespace Czim\CmsModels\Support\Data\Strategies;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class InstantiableClassStrategy
 *
 * @property object $instance
 * @property string $class
 * @property string $method
 * @property array  $parameters
 */
class InstantiableClassStrategy extends AbstractDataObject
{

    protected $attributes = [
        'instance'   => null,
        'class'      => null,
        'method'     => null,
        'parameters' => [],
    ];

}
