<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\View\FormFieldDisplayInterface;

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
