<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Carbon\Carbon;
use DateTime;

trait NormalizesDateValue
{

    /**
     * Normalizes a date value to a given output string format.
     *
     * Optionally takes an expected incoming format, for string values.
     *
     * @param string|DateTime $date
     * @param string          $outFormat
     * @param string|null     $inFormat
     * @param bool            $nullable    whether
     * @return null|string
     */
    public function normalizeDateValue($date, $outFormat = 'Y-m-d H:i:s', $inFormat = null, $nullable = false)
    {
        if (empty($outFormat)) {
            $outFormat = 'Y-m-d H:i:s';
        }

        if ($date instanceof DateTime) {
            return $date->format($outFormat);
        }

        if (null === $date) {

            if ($nullable) {
                return null;
            }

            return $this->makeEmptyStringDate($outFormat);
        }

        if (null === $inFormat) {

            try {

                $date = new Carbon($date);

            } catch (\Exception $e) {

                if ($nullable) {
                    return null;
                }

                return $this->makeEmptyStringDate($outFormat);
            }

        } else {

            $date = DateTime::createFromFormat($inFormat, $date);
        }

        return $date->format($outFormat);
    }

    /**
     * Returns non-NULL empty date as string.
     *
     * @param string $format
     * @return string
     */
    protected function makeEmptyStringDate($format = 'Y-m-d H:i:s')
    {
        // todo: somehow format this
        return '0000-00-00 00:00:00';
    }

}
