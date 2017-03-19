<?php
namespace Czim\CmsModels\Strategies\Form\Display;

class DateStrategy extends DateTimeStrategy
{
    const DEFAULT_FORMAT = 'Y-m-d';


    /**
     * @return string
     */
    protected function defaultMomentDateFormat()
    {
        return 'YYYY-MM-DD';
    }

}
