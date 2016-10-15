<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class BooleanCheckboxStrategy Extends AbstractDefaultStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.boolean_checkbox';
    }

}
