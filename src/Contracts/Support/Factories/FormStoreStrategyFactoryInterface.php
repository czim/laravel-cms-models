<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

interface FormStoreStrategyFactoryInterface
{

    /**
     * Makes a form store display strategy instance.
     *
     * @param string $strategy
     * @return FormStoreStrategyFactoryInterface
     */
    public function make($strategy);

}
