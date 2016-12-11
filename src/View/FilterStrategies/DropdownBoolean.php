<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Illuminate\Database\Eloquent\Builder;

class DropdownBoolean extends AbstractFilterStrategy
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

        if ('' === $value) {
            $value = null;
        }

        if (null !== $value) {
            $value = $value ? '1' : '0';
        }

        return view(
            'cms-models::model.partials.filters.dropdown-boolean',
            [
                'label'    => $info->label(),
                'key'      => $key,
                'selected' => $value,
                'options'  => [
                    '1' => $this->getTrueLabel(),
                    '0' => $this->getFalseLabel(),
                ],
            ]
        )->render();
    }

    /**
     * @return string
     */
    protected function getTrueLabel()
    {
        $label = array_get($this->filterInfo->options(), 'true_label_translated');
        if ($label) {
            return cms_trans($label);
        }

        $label = array_get($this->filterInfo->options(), 'true_label');
        if ($label) {
            return $label;
        }

        return cms_trans('common.boolean.true');
    }

    /**
     * @return string
     */
    protected function getFalseLabel()
    {
        $label = array_get($this->filterInfo->options(), 'false_label_translated');
        if ($label) {
            return cms_trans($label);
        }

        $label = array_get($this->filterInfo->options(), 'false_label');
        if ($label) {
            return $label;
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
