<?php
namespace Czim\CmsModels\View\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

trait GetsNestedRelations
{

    /**
     * Returns relation nested to any level for a dot notation source.
     *
     * Note that actual relation resolution can only be done for singular nested relations
     * (all the way down).
     *
     * @param Model  $model
     * @param string $source
     * @param bool   $actual    whether the relation should be returned for an actual model
     * @return Relation|null
     */
    protected function getNestedRelation(Model $model, $source, $actual = false)
    {
        if ($source instanceof Relation) {
            return $source;
        }

        // If the source is user-configured, resolve it

        $relationNames = explode('.', $source);
        $relationName  = array_shift($relationNames);

        if ( ! method_exists($model, $relationName)) {
            throw new UnexpectedValueException(
                'Model ' . get_class($model) . " does not have relation method {$relationName}"
            );
        }

        /** @var Relation $relation */
        $relation = $model->{$relationName}();

        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException(
                'Model ' . get_class($model) . ".{$relationName} is not an Eloquent relation"
            );
        }

        if ( ! count($relationNames)) {
            return $relation;
        }

        // Handle nested relation count if dot notation is used
        // We can do nesting for translations, if we assume the current locale's translation
        // todo

        if ($actual && ! $this->isRelationSingle($relation)) {
            throw new UnexpectedValueException(
                'Model ' . get_class($model) . ".{$relationName} does not allow deeper nesting (not to-one)"
            );
        }

        // Retrieve nested relation for actual or abstract context
        if ($actual) {
            $model = $relation->first();
        } else {
            $model = $relation->getRelated();
        }

        if ( ! $model) return null;

        return $this->getNestedRelation($model, implode('.', $relationNames));
    }

    /**
     * Returns a (nested) relation instance.
     *
     * @param Model $model
     * @param mixed $source
     * @return Relation|null
     */
    protected function getActualNestedRelation(Model $model, $source)
    {
        return $this->getNestedRelation($model, $source, true);
    }

    /**
     * Returns whether a relation is a to-one.
     *
     * @param mixed $relation
     * @return bool
     */
    protected function isRelationSingle($relation)
    {
        return (    $relation instanceof HasOne
                ||  $relation instanceof BelongsTo
                ||  $relation instanceof MorphTo
                );
    }

}
