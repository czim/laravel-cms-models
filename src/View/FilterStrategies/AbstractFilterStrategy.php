<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\View\FilterApplicationInterface;
use Czim\CmsModels\Contracts\View\FilterDisplayInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
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
     * Whether to combine any search terms with the OR operator.
     *
     * @var bool
     */
    protected $combineOr = false;

    /**
     * The parameters passed for the application of the strategy.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The model information for the model currently relevant for building the query.
     * This may be exchanged for nested relation models' information, if required & available.
     *
     * @var ModelInformationInterface|ModelInformation
     */
    protected $modelInfo;

    /**
     * Applies the filter value to the query.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @param array   $parameters
     */
    public function apply($query, $target, $value, $parameters = [])
    {
        // todo: detect special targets, including raw strategies

        $this->modelInfo  = $this->getModelInformation($query);
        $this->parameters = $parameters;

        $targets = $this->parseTargets($target);

        // If there is only a single target, do not group the condition.
        if (count($targets) == 1) {

            $this->applyForSingleTarget($query, head($targets), $value);

            return;
        }

        // If there are more, we need to group them and combine the conditions with 'or'.
        $query->where(function ($query) use ($targets, $value) {

            $this->combineOr = true;

            foreach ($targets as $singleTarget) {

                $this->applyForSingleTarget($query, $singleTarget, $value);
            }
        });
    }

    /**
     * Applies the filter for a single part of multiple targets
     *
     * @param Builder $query
     * @param array   $targetParts
     * @param mixed   $value
     */
    protected function applyForSingleTarget($query, array $targetParts, $value)
    {
        $this->translated = $this->isTranslatedTargetAttribute($targetParts, $query->getModel());

        // If we combine with the 'or' operator, group the filter's conditions
        // so the relation with the other filters is kept inclusive.
        if ($this->combineOr) {

            $query->where(function ($query) use ($targetParts, $value) {
                $this->applyRecursive($query, $targetParts, $value);
            });
            return;
        }

        $this->applyRecursive($query, $targetParts, $value);
    }

    /**
     * Parses string of (potentially) multiple targets to make a normalized array.
     *
     * @param string $targets
     * @return array    array of normalized target array
     */
    protected function parseTargets($targets)
    {
        $targets = array_map(
            [$this, 'parseTarget'],
            explode(',', $targets)
        );

        return $this->interpretSpecialTargets($targets);
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

        $whereHasMethod = $this->combineOr ? 'orWhereHas' : 'whereHas';

        return $query->{$whereHasMethod}(
            $relation,
            function ($query) use ($targetParts, $value) {
                return $this->applyRecursive($query, $targetParts, $value);
            }
        );
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
        $whereHasMethod = $this->combineOr ? 'orWhereHas' : 'whereHas';

        return $query->{$whereHasMethod}('translations', function ($query) use ($target, $value) {

            $this->applyLocaleRestriction($query);

            return $this->applyValue($query, $target, $value);
        });
    }

    /**
     * Interprets and translates special targets into separate target arrays.
     *
     * Think of a special target like '*', which should be translated into
     * separate condition targets for each (relevant) attribute of the model.
     *
     * @param array $targets
     * @return array
     */
    protected function interpretSpecialTargets(array $targets)
    {
        $normalized = [];

        foreach ($targets as $targetParts) {

            // Detect '*' and convert to all attributes
            if (count($targetParts) == 1 && trim(head($targetParts)) == '*') {
                $normalized += $this->makeTargetsForAllAttributes();
                continue;
            }

            $normalized[] = $targetParts;
        }

        return $normalized;
    }

    /**
     * Returns a targets array with normalized target parts for all relevant attributes
     * of the main query model.
     *
     * @return array
     */
    protected function makeTargetsForAllAttributes()
    {
        if ( ! $this->modelInfo) {
            return [];
        }

        $targets = [];

        foreach ($this->modelInfo->attributes as $key => $attribute) {
            if ( ! $this->isAttributeRelevant($attribute)) continue;

            $targets[] = $this->parseTarget($key);
        }

        return $targets;
    }

    /**
     * Returns whether given attribute data represents an attribute that is relevant
     * for performing the filter on.
     *
     * @param ModelAttributeData $attribute
     * @return bool
     */
    protected function isAttributeRelevant(ModelAttributeData $attribute)
    {
        return true;
    }

    /**
     * Returns model information instance for query, if possible.
     *
     * @param Builder $query
     * @return ModelInformation|false
     */
    protected function getModelInformation($query)
    {
        return $this->getModelInformationRepository()
            ->getByModel($query->getModel());
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
