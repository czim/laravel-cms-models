<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\View\FilterApplicationInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\View\Traits\HandlesTranslatedTarget;
use Illuminate\Database\Eloquent\Builder;

class BasicString implements FilterDisplayInterface, FilterApplicationInterface
{
    use HandlesTranslatedTarget;

    /**
     * Whether a single string match should be exact.
     *
     * @var bool
     */
    protected $exact = false;

    /**
     * Whether this filter is for a translated attribute.
     *
     * @var bool
     */
    protected $translated = false;


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
     * Applies the filter value for
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @param array   $parameters
     */
    public function apply($query, $target, $value, $parameters = [])
    {
        // todo: detect special targets, including raw strategies

        $targetParts = $this->parseTarget($target);

        $this->translated = $this->isTranslatedTargetAttribute($targetParts, $query->getModel());

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

            if ($this->translated) {
                return $this->applyTranslatedValue($query, head($targetParts), $value);
            }

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

        if ( ! $this->exact) {
            return $query->where($target, 'like', '%' . $value . '%');
        }

        return $query->where($target, $value);
    }

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @return mixed
     */
    protected function applyTranslatedValue($query, $target, $value)
    {
        return $query->whereHas('translations', function ($query) use ($target, $value) {

            $this->applyLocaleRestriction($query);

            return $this->applyValue($query, $target, $value);
        });
    }

}
