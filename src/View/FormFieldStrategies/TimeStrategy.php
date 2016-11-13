<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class TimeStrategy extends DateTimeStrategy
{

    /**
     * Returns default PHP date format.
     *
     * @return string
     */
    protected function defaultDateFormat()
    {
        return 'H:i';
    }

    /**
     * @return string
     */
    protected function defaultMomentDateFormat()
    {
        return 'HH:mm';
    }

}
