<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Carbon\Carbon;
use DateTime;

class DateTimeStrategy extends AbstractDefaultStrategy
{
    const DEFAULT_FORMAT = 'Y-m-d H:i';


    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.datepicker-datetime';
    }

    /**
     * Normalizes a value to make sure it can be processed uniformly.
     *
     * @param mixed $value
     * @param bool  $original   whether the value is the original value for the persisted model
     * @return mixed
     */
    protected function normalizeValue($value, $original = false)
    {
        return $value;
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        // Format the date value according to what the datepicker expects
        if ($data['value'] instanceof DateTime) {

            /** @var DateTime $value */
            $value = $data['value'];

            $format = array_get($data, 'options.format', $this->defaultDateFormat());

            if ($format) {
                $data['value'] = $value->format($format);
            }
        }

        // Prepare the momentJS date format, if not explicitly set
        if ( ! array_get($data, 'options.moment_format')) {

            $format       = array_get($data, 'options.format');
            $momentFormat = $format
                ?   $this->convertDateFormatToMoment($format)
                :   $this->defaultMomentDateFormat();

            array_set($data, 'options.moment_format', $momentFormat);
        }

        // Prepare special properties for datepicker
        if ($indicator = array_get($data, 'options.minimum_date')) {
            $data['minimumDate'] = $this->interpretDateIndicator($indicator);
        }
        if ($indicator = array_get($data, 'options.maximum_date')) {
            $data['maximumDate'] = $this->interpretDateIndicator($indicator);
        }

        $data['excludedDates'] = [];
        if (($indicators = array_get($data, 'options.excluded_dates')) && is_array($indicators)) {
            $data['excludedDates'] = array_map([ $this, 'interpretDateIndicator' ], $indicators);
        }

        return $data;
    }

    /**
     * Interprets a date indicator string value as a date time string relative to the current date.
     *
     * @param string $indicator
     * @return string
     */
    protected function interpretDateIndicator($indicator)
    {
        if ( ! is_string($indicator)) {
            throw new \UnexpectedValueException('Unexpected date indicator value: ' . print_r($indicator, true));
        }

        if ('now' === strtolower($indicator)) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        if (starts_with($indicator, '-')) {
            return Carbon::now()->sub(
                new \DateInterval(substr($indicator, 1))
            )->format('Y-m-d H:i:s');
        }
        if (starts_with($indicator, '+')) {
            return Carbon::now()->add(
                new \DateInterval(substr($indicator, 1))
            )->format('Y-m-d H:i:s');
        }

        return (new Carbon($indicator))->format('Y-m-d H:i:s');
    }

    /**
     * Returns default PHP date format.
     *
     * @return string
     */
    protected function defaultDateFormat()
    {
        return static::DEFAULT_FORMAT;
    }

    /**
     * Returns default MomentJS date format.
     *
     * @return string
     */
    protected function defaultMomentDateFormat()
    {
        return 'YYYY-MM-DD HH:mm';
    }

    /**
     * Converts PHP date format to MommentJS date format.
     *
     * @param string $format    PHP date format string
     * @return string
     */
    protected function convertDateFormatToMoment($format)
    {
        $replacements = [
            'd' => 'DD',
            'D' => 'ddd',
            'j' => 'D',
            'l' => 'dddd',
            'N' => 'E',
            'S' => 'o',
            'w' => 'e',
            'z' => 'DDD',
            'W' => 'W',
            'F' => 'MMMM',
            'm' => 'MM',
            'M' => 'MMM',
            'n' => 'M',
            't' => '', // no equivalent
            'L' => '', // no equivalent
            'o' => 'YYYY',
            'Y' => 'YYYY',
            'y' => 'YY',
            'a' => 'a',
            'A' => 'A',
            'B' => '', // no equivalent
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => 'SSS',
            'e' => 'zz', // deprecated since version 1.6.0 of moment.js
            'I' => '', // no equivalent
            'O' => '', // no equivalent
            'P' => '', // no equivalent
            'T' => '', // no equivalent
            'Z' => '', // no equivalent
            'c' => '', // no equivalent
            'r' => '', // no equivalent
            'U' => 'X',
        ];

        foreach ($replacements as $from => $to) {
            $replacements['\\' . $from] = '[' . $from . ']';
        }

        return strtr($format, $replacements);
    }

}
