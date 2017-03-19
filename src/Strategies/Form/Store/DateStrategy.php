<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Strategies\Form\Display as FormFieldDisplayStrategies;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
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
        // todo: would be nice if we could make a date from no matter the format
        $format = $this->getExpectedDateFormat();

        $value = trim($value);

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

        if (empty($value)) {
            $value = null;
        }

        return $value;
    }

    /**
     * Returns validation rules specific for the strategy.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return array|false|null null to fall back to default rules.
     */
    protected function getStrategySpecificRules(ModelFormFieldDataInterface $field = null)
    {
        $format = $this->getExpectedDateFormat();

        if ( ! $format) {
            return [ 'date' ];
        }

        return [ 'date_format:' . $format ];
    }

    /**
     * Returns format expected from the display strategy.
     *
     * @return null|string
     */
    protected function getExpectedDateFormat()
    {
        $format = array_get($this->formFieldData->options(), 'format');

        if ($format) {
            return $format;
        }

        switch ($this->formFieldData->display_strategy) {

            case FormDisplayStrategy::DATEPICKER_DATETIME:
                return FormFieldDisplayStrategies\DateTimeStrategy::DEFAULT_FORMAT;

            case FormDisplayStrategy::DATEPICKER_DATE:
                return FormFieldDisplayStrategies\DateStrategy::DEFAULT_FORMAT;

            case FormDisplayStrategy::DATEPICKER_TIME:
                return FormFieldDisplayStrategies\TimeStrategy::DEFAULT_FORMAT;
        }

        return null;
    }

}
