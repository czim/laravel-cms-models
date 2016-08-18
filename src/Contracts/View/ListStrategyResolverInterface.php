<?php
namespace Czim\CmsModels\Contracts\View;

interface ListStrategyResolverInterface
{

    /**
     * Resolves a list strategy value to a normalized identifier.
     *
     * @param string $strategy
     * @return string|null
     */
    public function resolve($strategy);

}
