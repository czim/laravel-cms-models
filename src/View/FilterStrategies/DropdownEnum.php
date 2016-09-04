<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class DropdownEnum extends AbstractFilterStrategy
{

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
        $values = $this->normalizeValues($info->values());

        return view(
            'cms-models::model.partials.filters.dropdown-enum',
            [
                'label'    => $info->label(),
                'key'      => $key,
                'selected' => $value,
                'options'  => $values,
            ]
        )->render();
    }

    /**
     * Normalizes values to associative key => display pairs.
     *
     * @param array $values
     * @return array    associative
     */
    protected function normalizeValues(array $values)
    {
        if (Arr::isAssoc($values)) {
            return $values;
        }

        $normalized = [];

        foreach ($values as $value) {
            $normalized[ $value ] = $value;
        }

        return $normalized;
    }

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @return mixed
     */
    protected function applyValue($query, $target, $value)
    {
        if (is_array($value)) {
            return $query->whereIn($target, $value);
        }

        return $query->where($target, $value);
    }

}
