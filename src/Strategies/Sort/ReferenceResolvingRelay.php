<?php
namespace Czim\CmsModels\Strategies\Sort;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Strategies\SortStrategyInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

/**
 * Class ReferenceResolvingRelay
 *
 * Sorting strategy that attempts to find the best available sorting
 * strategy for the column on the model for the query. No fallback
 * sorting is applied if no viable candidate is found.
 *
 * The main purpose for this is to get the best sorting possible
 * from the limited context of a model reference lookup.
 */
class ReferenceResolvingRelay extends AbstractSortStrategy
{

    /**
     * Applies the sort to a query/model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $direction     asc|desc
     * @return Builder
     */
    public function apply($query, $column, $direction = 'asc')
    {
        // Determine the best strategy to use for the column
        // (which may not even be a column on the model's own table)

        $strategy = $this->determineStrategy($query->getModel(), $column);

        if ($strategy) {

            $instance = new $strategy;

            if ( ! ($instance instanceof SortStrategyInterface)) {
                throw new UnexpectedValueException("{$strategy} is not a sort strategy");
            }

            $instance->apply($query, $column, $direction);
        }

        return $query;
    }

    /**
     * Returns classname for the strategy to use or false if no strategy could be determined.
     *
     * @param Model  $model
     * @param string $column
     * @return false|string
     */
    protected function determineStrategy(Model $model, $column)
    {
        if ($this->isColumnTranslated($model, $column)) {
            return $this->getStrategyForTranslated();
        }

        if ($this->isColumnOnRelatedModel($model, $column)) {
            return $this->getStrategyForRelatedModelAttribute();
        }

        if ($this->isColumnOnModelTable($model, $column)) {
            return $this->getStrategyForDirectAttribute();
        }

        return false;
    }

    /**
     * @return string|false
     */
    protected function getStrategyForTranslated()
    {
        return TranslatedAttribute::class;
    }

    /**
     * @return string|false
     */
    protected function getStrategyForRelatedModelAttribute()
    {
        // For now, we won't attempt to make complicated joins to make this work.
        return false;
    }

    /**
     * @return string|false
     */
    protected function getStrategyForDirectAttribute()
    {
        return NullLast::class;
    }

    /**
     * Returns whether the column is a translated attribute of the model.
     *
     * @param Model  $model
     * @param string $column
     * @return bool
     */
    protected function isColumnTranslated(Model $model, $column)
    {
        $info = $this->getModelInformation($model);

        if ($info) {
            return (    $info->translated
                    &&  array_key_exists($column, $info->attributes)
                    &&  $info->attributes[$column]->translated
                    );
        }

        // Determine based on model itself
        if ( ! $this->hasTranslatableTrait($model)) return false;

        /** @var Translatable $model */
        return $model->isTranslationAttribute($column);
    }

    /**
     * Returns whether the column is an attribute of a related model.
     *
     * @param Model  $model
     * @param string $column
     * @return bool
     */
    protected function isColumnOnRelatedModel(Model $model, $column)
    {
        return false !== strpos($column, '.');
    }

    /**
     * Returns whether the column is a direct attribute of model.
     *
     * @param Model  $model
     * @param string $column
     * @return bool
     */
    protected function isColumnOnModelTable(Model $model, $column)
    {
        $info = $this->getModelInformation($model);

        if ($info) {
            return (    array_key_exists($column, $info->attributes)
                    &&  ! $info->attributes[$column]->translated
                    );
        }

        // Determine based on model itself
        return ($model->getKeyName() == $column || $model->isFillable($column) || $model->isGuarded($column));
    }

    /**
     * @param Model|string $model
     * @return null|ModelInformationInterface|ModelInformation
     */
    protected function getModelInformation($model)
    {
        /** @var ModelInformationRepositoryInterface $repository */
        $repository = app(ModelInformationRepositoryInterface::class);

        if ($model instanceof Model) {
            return $repository->getByModel($model);
        }

        return $repository->getByModelClass($model);
    }


    /**
     * Returns whether a class has the translatable trait.
     *
     * @param mixed $class
     * @return bool
     */
    protected function hasTranslatableTrait($class)
    {
        $translatable = config('cms-models.analyzer.traits.translatable', []);

        if ( ! count($translatable)) {
            return false;
        }

        return (bool) count(array_intersect($this->classUsesDeep($class), $translatable));
    }

    /**
     * Returns all traits used by a class (at any level).
     *
     * @param mixed $class
     * @return string[]
     */
    protected function classUsesDeep($class)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while ( ! empty($traitsToSearch)) {
            $newTraits      = class_uses(array_pop($traitsToSearch));
            $traits         = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait), $traits);
        }

        return array_unique($traits);
    }

}
