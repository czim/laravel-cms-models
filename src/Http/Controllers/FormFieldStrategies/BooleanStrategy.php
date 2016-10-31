<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

class BooleanStrategy extends AbstractFormFieldStoreStrategy
{

    /**
     * Adjusts a value for nullable fields, if required, to prevent null values being set.
     *
     * @param mixed $value
     * @return null
     */
    protected function adjustValue($value)
    {
        if ($this->isNullable() && null === $value) {
            return null;
        }

        return (bool) $value;
    }

}
