<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Illuminate\Database\Eloquent\Builder;

class BasicString extends AbstractFilterStrategy
{

    /**
     * Whether a single string match should be exact.
     *
     * @var bool
     */
    protected $exact = false;

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
        return view(
            'cms-models::model.partials.filters.basic-string',
            [
                'label' => $info->label(),
                'key'   => $key,
                'value' => $value,
            ]
        )->render();
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

        if ( ! $this->exact) {
            return $query->where($target, 'like', '%' . $value . '%');
        }

        return $query->where($target, $value);
    }

}
