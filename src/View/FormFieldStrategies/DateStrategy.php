<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class DateStrategy extends DateTimeStrategy
{

    /**
     * Returns default PHP date format.
     *
     * @return string
     */
    protected function defaultDateFormat()
    {
        return 'Y-m-d';
    }

    /**
     * @return string
     */
    protected function defaultMomentDateFormat()
    {
        return 'YYYY-MM-DD';
    }

}
