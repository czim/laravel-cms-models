<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class DropdownBoolean extends AbstractFilterStrategy
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
        if ('' === $value) {
            $value = null;
        }

        if (null !== $value) {
            $value = $value ? '1' : '0';
        }

        return view(
            'cms-models::model.partials.filters.dropdown-boolean',
            [
                'label'    => $this->filterData ? $this->filterData->label() : $key,
                'key'      => $key,
                'selected' => $value,
                'options'  => [
                    '1' => $this->getTrueLabel(),
                    '0' => $this->getFalseLabel(),
                ],
            ]
        );
    }

    /**
     * @return string
     */
    protected function getTrueLabel()
    {
        if ($this->filterData) {

            $label = array_get($this->filterData->options(), 'true_label_translated');
            if ($label) {
                return cms_trans($label);
            }

            $label = array_get($this->filterData->options(), 'true_label');
            if ($label) {
                return $label;
            }
        }

        return cms_trans('common.boolean.true');
    }

    /**
     * @return string
     */
    protected function getFalseLabel()
    {
        if ($this->filterData) {

            $label = array_get($this->filterData->options(), 'false_label_translated');
            if ($label) {
                return cms_trans($label);
            }

            $label = array_get($this->filterData->options(), 'false_label');
            if ($label) {
                return $label;
            }
        }

        return cms_trans('common.boolean.false');
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
