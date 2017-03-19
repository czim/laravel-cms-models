<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class DateRangeStrategy extends DefaultStrategy
{

    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        return [
            'from' => parent::retrieve($model, $this->getAttributeFrom()),
            'to'   => parent::retrieve($model, $this->getAttributeTo()),
        ];
    }

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
        if (preg_match('#^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}$#', $value, $matches)) {
            $value .= ':00';
        } elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value, $matches)) {
            $value .= ' 00:00:00';
        }

        return $value;
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        $from = $this->getAttributeFrom();
        $to   = $this->getAttributeTo();

        $fromValue = $this->adjustValue(array_get($value, 'from'));
        $toValue   = $this->adjustValue(array_get($value, 'to'));

        $model->{$from} = $fromValue;
        $model->{$to}   = $toValue;
    }

    /**
     * Returns validation rules specific for the strategy.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return array|false|null     null to fall back to default rules.
     */
    protected function getStrategySpecificRules(ModelFormFieldDataInterface $field = null)
    {
        $format = $this->getExpectedDateFormat();

        $key = $this->formFieldData->key();

        if ( ! $format) {
            return [
                $key . '.from' => [ 'date' ],
                $key . '.to'   => [ 'date' ],
            ];
        }

        return [
            $key . '.from' => [ 'date_format:' . $format ],
            $key . '.to'   => [ 'date_format:' . $format ],
        ];
    }

    /**
     * Returns attribute name for the 'from' date.
     *
     * @return string
     */
    protected function getAttributeFrom()
    {
        $attribute = array_get($this->formFieldData->options(), 'from', $this->formFieldData->source());

        if ( ! $attribute) {
            throw new UnexpectedValueException("DateRangeStrategy must have 'date_from' option set");
        }

        return $attribute;
    }

    /**
     * Returns attribute name for the 'to' date.
     *
     * @return string
     */
    protected function getAttributeTo()
    {
        $attribute = array_get($this->formFieldData->options(), 'to');

        if ( ! $attribute) {
            throw new UnexpectedValueException("DateRangeStrategy must have 'date_to' option set");
        }

        return $attribute;
    }

    /**
     * Returns format expected from the display strategy.
     *
     * @return null|string
     */
    protected function getExpectedDateFormat()
    {
        $format = array_get($this->formFieldData->options(), 'format');

        return $format ?: null;
    }

}
