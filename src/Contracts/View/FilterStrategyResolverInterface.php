<?php
namespace Czim\CmsModels\Contracts\View;

interface FilterStrategyResolverInterface
{

    /**
     * Resolves a filter strategy value to a normalized identifier.
     *
     * @param string $strategy
     * @return string|null
     */
    public function resolve($strategy);

}
