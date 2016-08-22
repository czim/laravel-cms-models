<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\View\FilterStrategyResolverInterface;

class FilterStrategyResolver implements FilterStrategyResolverInterface
{

    /**
     * Resolves a filter strategy value to a normalized identifier.
     *
     * @param string $strategy
     * @return string|null
     */
    public function resolve($strategy)
    {
        switch ($strategy) {

            default:
                return null;
        }
    }

}
