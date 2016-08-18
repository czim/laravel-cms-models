<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\View\ListStrategyResolverInterface;
use Czim\CmsModels\Support\Enums\AttributeFormStrategy;

class ListStrategyResolver implements ListStrategyResolverInterface
{

    /**
     * Resolves a list strategy value to a normalized identifier.
     *
     * @param string $strategy
     * @return string|null
     */
    public function resolve($strategy)
    {
        switch ($strategy) {

            case AttributeFormStrategy::BOOLEAN_CHECKBOX:
            case AttributeFormStrategy::BOOLEAN_DROPDOWN:
                return 'Checkbox';

            default:
                return null;
        }
    }

}
