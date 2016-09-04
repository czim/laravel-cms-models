<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\View\FilterApplicationInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;
use Czim\CmsModels\View\Traits\HandlesTranslatedTarget;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractFilterStrategy implements FilterDisplayInterface, FilterApplicationInterface
{
    use HandlesTranslatedTarget;

    /**
     * Whether this filter is for a translated attribute.
     *
     * @var bool
     */
    protected $translated = false;

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
     * Applies a value directly to a builder object.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @return mixed
     */
    abstract protected function applyValue($query, $target, $value);

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
