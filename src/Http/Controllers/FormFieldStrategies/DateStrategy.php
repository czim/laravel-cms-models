<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Support\Enums\FormDisplayStrategy;

class DateStrategy extends DefaultStrategy
{

    /**
     * Adjusts or normalizes a value before storing it.
     *
     * Makes sure the date format is what Carbon expects.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        // todo: would be nice if we could make a date from no matter
        // what format was configured. We can do this later, once
        // we've settled on a way to configure/determine formats

        switch ($this->formFieldData->display_strategy) {

            case FormDisplayStrategy::DATEPICKER_DATETIME:
                // Add seconds if they're missing
                if (preg_match('#^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}$#', $value, $matches)) {
                    $value .= ':00';
                }
                break;

            case FormDisplayStrategy::DATEPICKER_DATE:
                // Add time if it's missing
                if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value, $matches)) {
                    $value .= ' 00:00:00';
                }
                break;

            case FormDisplayStrategy::DATEPICKER_TIME:
                break;
        }

        return $value;
    }

}
