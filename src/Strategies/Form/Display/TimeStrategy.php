<?php
namespace Czim\CmsModels\Strategies\Form\Display;

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
