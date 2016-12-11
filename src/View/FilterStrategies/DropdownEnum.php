<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\View\DropdownStrategyInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Illuminate\Database\Eloquent\Builder;
use MyCLabs\Enum\Enum;

class DropdownEnum extends AbstractFilterStrategy
{

    /**
     * @var ModelFilterDataInterface|ModelListFilterData
     */
    protected $filterInfo;

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string  $key
     * @param mixed   $value
     * @param ModelFilterDataInterface|ModelListFilterData $info
     * @return string
     */
    public function render($key, $value, ModelFilterDataInterface $info)
    {
        $this->filterInfo = $info;

        return view(
            'cms-models::model.partials.filters.dropdown-enum',
            [
                'label'    => $info->label(),
                'key'      => $key,
                'selected' => $value,
                'options'  => $this->getDropdownOptions(),
            ]
        )->render();
    }

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder   $query
     * @param string    $target
     * @param mixed     $value
     * @param null|bool $combineOr    overrides global value if non-null
     * @return mixed
     */
    protected function applyValue($query, $target, $value, $combineOr = null)
    {
        $combineOr = $combineOr === null ? $this->combineOr : $combineOr;

        if (is_array($value)) {
            return $query->whereIn($target, $value, $combineOr ? 'or' : 'and');
        }

        return $query->where($target, '=', $value, $combineOr ? 'or' : 'and');
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
        if ($source = array_get($this->filterInfo->options(), 'value_source')) {

            $values = $this->getDropdownValuesFromSource($source);

            if (false !== $values) {
                return $values;
            }
        }

        return array_get($this->filterInfo->options(), 'values');
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
        if ($source = array_get($this->filterInfo->options(), 'label_source')) {

            $labels = $this->getDropdownLabelsFromSource($source);

            if (false !== $labels) {
                return $labels;
            }
        }

        $labels = array_get($this->filterInfo->options(), 'labels_translated', []);

        if (count($labels)) {
            return array_map('cms_trans', $labels);
        }

        return array_get($this->filterInfo->options(), 'labels', []);
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
