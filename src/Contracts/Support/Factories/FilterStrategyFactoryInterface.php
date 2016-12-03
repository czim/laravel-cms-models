<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\View\FilterApplicationInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;

interface FilterStrategyFactoryInterface
{

    /**
     * Makes a filter display instance.
     *
     * @param string                        $strategy
     * @param string|null                   $key
     * @param ModelFilterDataInterface|null $info
     * @return FilterDisplayInterface
     */
    public function makeForDisplay($strategy, $key = null, ModelFilterDataInterface $info = null);

    /**
     * Make a filter application instance.
     *
     * @param string                        $strategy
     * @param string|null                   $key
     * @param ModelFilterDataInterface|null $info
     * @return FilterApplicationInterface
     */
    public function makeForApplication($strategy, $key = null, ModelFilterDataInterface $info = null);

}
