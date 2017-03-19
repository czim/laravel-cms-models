<?php
namespace Czim\CmsModels\Strategies\Form\Display;

class DateRangeStrategy extends DateTimeStrategy
{
    const DEFAULT_FORMAT = 'Y-m-d';


    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.datepicker-range';
    }

    /**
     * @return string
     */
    protected function defaultMomentDateFormat()
    {
        return 'YYYY-MM-DD';
    }

}
