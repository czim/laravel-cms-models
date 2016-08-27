<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\View\FilterApplicationInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class DropdownEnum implements FilterDisplayInterface, FilterApplicationInterface
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
     * Applies the filter value
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @param array   $parameters
     */
    public function apply($query, $target, $value, $parameters = [])
    {
        $targetParts = $this->parseTarget($target);

        $this->applyRecursive($query, $targetParts, $value);
    }

    /**
     * Applies a the filter value recursively for normalized target segments.
     *
     * @param Builder  $query
     * @param string[] $targetParts
     * @param mixed    $value
     * @return mixed
     */
    protected function applyRecursive($query, array $targetParts, $value)
    {
        if (count($targetParts) < 2) {
            return $this->applyValue($query, head($targetParts), $value);
        }

        $relation = array_shift($targetParts);

        return $query->whereHas($relation, function ($query) use ($targetParts, $value) {
            return $this->applyRecursive($query, $targetParts, $value);
        });
    }

    /**
     * Parses target to make a normalized array.
     *
     * @param string $target
     * @return array
     */
    protected function parseTarget($target)
    {
        return explode('.', $target);
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
