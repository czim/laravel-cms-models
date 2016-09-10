<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Illuminate\Database\Eloquent\Builder;

class DropdownBoolean extends AbstractFilterStrategy
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
        if ('' === $value) {
            $value = null;
        }

        if (null !== $value) {
            $value = $value ? '1' : '0';
        }

        return view(
            'cms-models::model.partials.filters.dropdown-enum',
            [
                'label'    => $info->label(),
                'key'      => $key,
                'selected' => $value,
                'options'  => [
                    '1' => 'true',
                    '0' => 'false',
                ],
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

}
