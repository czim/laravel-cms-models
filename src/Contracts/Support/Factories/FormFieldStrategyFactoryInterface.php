<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Strategies\FormFieldDisplayInterface;

interface FormFieldStrategyFactoryInterface
{

    /**
     * Makes a form field display strategy instance.
     *
     * @param string $strategy
     * @return FormFieldDisplayInterface
     */
    public function make($strategy);

}
