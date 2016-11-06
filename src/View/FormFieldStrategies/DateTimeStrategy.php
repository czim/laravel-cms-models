<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class DateTimeStrategy extends AbstractDefaultStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.datepicker-datetime';
    }

}
