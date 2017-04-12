<?php
namespace Czim\CmsModels\Strategies\Filter;

use Czim\CmsModels\Contracts\Strategies\DropdownStrategyInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use MyCLabs\Enum\Enum;

class DropdownEnum extends AbstractFilterStrategy
{

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string  $key
     * @param mixed   $value
     * @return string|View
     */
    public function render($key, $value)
    {
        return view(
            'cms-models::model.partials.filters.dropdown-enum',
            [
                'label'    => $this->filterData ? $this->filterData->label() : $key,
                'key'      => $key,
                'selected' => $value,
                'options'  => $this->getDropdownOptions(),
            ]
        );
    }

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder   $query
     * @param string    $target
     * @param mixed     $value
     * @param null|bool $combineOr    overrides global value if non-null
     * @param bool      $isFirst      whether this is the first expression (between brackets)
     * @return mixed
     */
    protected function applyValue($query, $target, $value, $combineOr = null, $isFirst = false)
    {
        $combineOr = ! $isFirst && ($combineOr === null ? $this->combineOr : $combineOr);
        $combine   = $combineOr ? 'or' : 'and';

        if (is_array($value)) {
            return $query->whereIn($target, $value, $combine);
        }

        return $query->where($target, '=', $value, $combine);
    }


    /**
     * Returns dropdown options as an associative array with display labels for values.
     *
     * @return array
     */
    protected function getDropdownOptions()
    {
        $values = $this->getDropdownValues();
        $labels = $this->getDropdownLabels();

        if ( ! $values) {
            return [];
        }

        // Make sure that labels are set for each value
        foreach ($values as $value) {
            if (isset($labels[ $value ])) continue;

            $labels[ $value ] = $value;
        }

        return array_intersect_key($labels, array_flip($values));
    }

    /**
     * Returns values to include in the dropdown.
     *
     * @return string[]
     */
    protected function getDropdownValues()
    {
        if ( ! $this->filterData) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        if ($source = array_get($this->filterData->options(), 'value_source')) {

            $values = $this->getDropdownValuesFromSource($source);

            if (false !== $values) {
                return $values;
            }
        }

        return array_get($this->filterData->options(), 'values');
    }

    /**
     * Returns values from a source FQN, if possible.
     *
     * @param string $source
     * @return string[]|false
     */
    protected function getDropdownValuesFromSource($source)
    {
        if (is_a($source, Enum::class, true)) {
            /** @var Enum $source */
            return array_map(
                function ($value) { return (string) $value; },
                $source::values()
            );
        }

        if (is_a($source, DropdownStrategyInterface::class, true)) {
            /** @var DropdownStrategyInterface $source */
            $source = app($source);
            return $source->values();
        }

        return false;
    }

    /**
     * Returns display labels to show in the dropdown, keyed by value.
     *
     * @return string[]
     */
    protected function getDropdownLabels()
    {
        if ( ! $this->filterData) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        if ($source = array_get($this->filterData->options(), 'label_source')) {

            $labels = $this->getDropdownLabelsFromSource($source);

            if (false !== $labels) {
                return $labels;
            }
        }

        $labels = array_get($this->filterData->options(), 'labels_translated', []);

        if (count($labels)) {
            return array_map('cms_trans', $labels);
        }

        return array_get($this->filterData->options(), 'labels', []);
    }

    /**
     * Returns display labels from a source FQN, if possible.
     *
     * @param string $source
     * @return string[]|false
     */
    protected function getDropdownLabelsFromSource($source)
    {
        if (is_a($source, DropdownStrategyInterface::class, true)) {
            /** @var DropdownStrategyInterface $source */
            $source = app($source);
            return $source->labels();
        }

        return false;
    }

}
