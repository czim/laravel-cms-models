<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Carbon\Carbon;
use Czim\CmsModels\Support\Strategies\Traits\NormalizesDateValue;
use Illuminate\Database\Eloquent\Builder;

class Datepicker extends AbstractFilterStrategy
{
    use NormalizesDateValue;

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string  $key
     * @param mixed   $value
     * @return string
     */
    public function render($key, $value)
    {
        return view(
            'cms-models::model.partials.filters.datepicker',
            [
                'label'   => $this->filterData ? $this->filterData->label() : $key,
                'key'     => $key,
                'value'   => $value,
                'options' => $this->filterData ? $this->filterData->options() : [],
            ]
        )->render();
    }

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @param null|bool $combineOr    overrides global value if non-null
     * @return mixed
     */
    protected function applyValue($query, $target, $value, $combineOr = null)
    {
        // Normalize the value according to the expected format
        // If the column is not date-only, do a between start/end time for the date set

        if ($this->filterData) {
            $format = array_get($this->filterData->options(), 'format');
        } else {
            $format = null;
        }

        $value = $this->normalizeDateValue($value, $format);

        // If the format the value is in is date-only, then we can do a simple equals check
        // if not, the date should be looked up as a range from start to end.

        if ($this->isTargetDateOnly()) {

            $query->where($target, '=', $value);

        } else {

            $dayStart = (new Carbon($value))->startOfDay();
            $dayEnd   = (new Carbon($value))->endOfDay();

            $query
                ->where($target, '>=', $dayStart)
                ->where($target, '<=', $dayEnd);
        }

        return $query;
    }

    /**
     * Returns whether the date format contains no time element.
     *
     * @return bool
     */
    protected function isFormatDateOnly()
    {
        if ($this->filterData) {
            $format = array_get($this->filterData->options(), 'format');
        } else {
            $format = null;
        }

        // Default is to filter date only
        if ( ! $format) {
            return true;
        }

        return ! preg_match('#[BgGhHisuvcU]#', $format);
    }

    /**
     * Returns whether the target date column is date only.
     *
     * @return bool     defaults to false if unknown.
     */
    protected function isTargetDateOnly()
    {
        if ( ! $this->filterData) {
            return false;
        }

        $target = $this->filterData->target();

        if ( ! array_key_exists($target, $this->getModelInformation()->attributes)) {
            return false;
        }

        // Everything but 'date' has time element ('datetime', 'timestamp', 'time')
        return ($this->getModelInformation()->attributes[ $target ]->type == 'date');
    }

}
