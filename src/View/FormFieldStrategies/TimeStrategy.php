<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class TimeStrategy extends DateTimeStrategy
{
    const DEFAULT_FORMAT = 'H:i';


    /**
     * @return string
     */
    protected function defaultMomentDateFormat()
    {
        return 'HH:mm';
    }

}
