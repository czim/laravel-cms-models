<?php
namespace Czim\CmsModels\Strategies\Reference;

use Czim\CmsModels\Contracts\View\ReferenceStrategyInterface;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesSourceStrategies;

abstract class AbstractReferenceStrategy implements ReferenceStrategyInterface
{
    use ResolvesSourceStrategies;


}
