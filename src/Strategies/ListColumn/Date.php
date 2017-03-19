<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class Date extends AbstractListDisplayStrategy
{

    /**
     * @var string
     */
    protected $format = 'Y-m-d';


    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        $source = $this->resolveModelSource($model, $source);

        $date = $this->interpretAsDate($source);

        if ( ! $date) {
            return '';
        }

        return e($date->format($this->getFormat()));
    }

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string|null
     */
    public function style(Model $model, $source)
    {
        return 'column-center';
    }

    /**
     * Parses a source value as a boolean value.
     *
     * @param mixed $value
     * @return DateTime|null
     */
    protected function interpretAsDate($value)
    {
        if (null === $value || 0 === $value || '' === $value) {
            return null;
        }

        if ($value instanceof DateTime) {
            return $value;
        }

        if (is_string($value)) {
            return new Carbon($value);
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        throw new UnexpectedValueException("Expecting null or date");
    }

    /**
     * @return string
     */
    protected function getFormat()
    {
        return array_get($this->options(), 'format', $this->format);
    }

}
