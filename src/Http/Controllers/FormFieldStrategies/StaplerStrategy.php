<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

class StaplerStrategy extends DefaultStrategy
{

    /**
     * Adjusts or normalizes a value before storing it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        // If the value is empty, use the stapler null value instead
        if (empty($value)) {
            return STAPLER_NULL;
        }

        return $value;
    }

}
