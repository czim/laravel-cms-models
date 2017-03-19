<?php
namespace Czim\CmsModels\Strategies\Filter;

use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Strategies\FilterStrategyInterface;
use Czim\CmsModels\Filters\ModelFilterData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Strategies\Traits\HandlesTranslatedTarget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractFilterStrategy implements FilterStrategyInterface
{
    use HandlesTranslatedTarget;

    /**
     * Whether this filter is for a translated attribute.
     *
     * @var bool
     */
    protected $translated = false;

    /**
     * @var ModelFilterDataInterface|ModelFilterData
     */
    protected $filterData;

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
     * The currently relevant query's model.
     *
     * @var null|Model
     */
    protected $model;

    /**
     * The model information for the model currently relevant for building the query.
     * This may be exchanged for nested relation models' information, if required & available.
     *
     * @var ModelInformationInterface|ModelInformation
     */
    protected $modelInfo;


    /**
     * Sets the filter's data.
     *
     * @param ModelFilterDataInterface|ModelFilterData $data
     * @return $this
     */
    public function setFilterInformation(ModelFilterDataInterface $data)
    {
        $this->filterData = $data;

        return $this;
    }

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
        $this->model      = $query->getModel();
        $this->parameters = $parameters;

        $targets = $this->parseTargets($target);

        // If there is only a single target, do not group the condition.
        if (count($targets) == 1) {

            $this->applyForSingleTarget($query, head($targets), $value, true);

            return;
        }

        // If we combine with the 'or' operator, group the filter's conditions
        // so the relation with the other filters is kept inclusive.

        $query->where(function ($query) use ($targets, $value) {

            $this->combineOr = true;

            foreach ($targets as $index => $singleTarget) {

                $this->applyForSingleTarget($query, $singleTarget, $value, $index < 1);
            }
        });
    }

    /**
     * Applies the filter for a single part of multiple targets
     *
     * @param Builder $query
     * @param array   $targetParts
     * @param mixed   $value
     * @param bool    $isFirst      whether this is the first expression (between brackets)
     */
    protected function applyForSingleTarget($query, array $targetParts, $value, $isFirst = false)
    {
        $this->translated = $this->isTranslatedTargetAttribute($targetParts, $query->getModel());

        $this->applyRecursive($query, $targetParts, $value, $isFirst);
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
     * @param bool     $isFirst      whether this is the first expression (between brackets)
     * @return mixed
     */
    protected function applyRecursive($query, array $targetParts, $value, $isFirst = false)
    {
        if (count($targetParts) < 2) {

            if ($this->translated) {
                return $this->applyTranslatedValue($query, head($targetParts), $value, $isFirst);
            }

            return $this->applyValue($query, head($targetParts), $value, null, $isFirst);
        }

        $relation = array_shift($targetParts);

        $whereHasMethod = ! $isFirst && $this->combineOr ? 'orWhereHas' : 'whereHas';

        return $query->{$whereHasMethod}(
            $relation,
            function ($query) use ($targetParts, $value) {
                return $this->applyRecursive($query, $targetParts, $value, true);
            }
        );
    }

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder   $query
     * @param string    $target
     * @param mixed     $value
     * @param null|bool $combineOr    overrides global value if non-null
     * @param bool      $isFirst      whether this is the first expression (between brackets)
     * @return mixed
     */
    abstract protected function applyValue($query, $target, $value, $combineOr = null, $isFirst = false);

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder $query
     * @param string  $target
     * @param mixed   $value
     * @param bool    $isFirst      whether this is the first expression (between brackets)
     * @return mixed
     */
    protected function applyTranslatedValue($query, $target, $value, $isFirst = false)
    {
        $whereHasMethod = ! $isFirst && $this->combineOr ? 'orWhereHas' : 'whereHas';

        return $query->{$whereHasMethod}('translations', function ($query) use ($target, $value) {

            $this->applyLocaleRestriction($query);

            return $this->applyValue($query, $target, $value, false, true);
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
        $modelInfo = $this->getModelInformation();

        if ( ! $modelInfo) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $targets = [];

        foreach ($modelInfo->attributes as $key => $attribute) {
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
     * @return ModelInformation|false
     */
    protected function getModelInformation()
    {
        if (null === $this->model) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return $this->getModelInformationRepository()->getByModel($this->model);
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
