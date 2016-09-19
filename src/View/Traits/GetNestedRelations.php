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
     * Returns a (nested) relation to get counts for.
     *
     * @param Model $model
     * @param mixed $source
     * @return mixed
     */
    protected function getRelation(Model $model, $source)
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

        if ( ! $this->isRelationSingle($relation)) {
            throw new UnexpectedValueException(
                'Model ' . get_class($model) . ".{$relationName} does not allow deeper nesting (not a to-one)"
            );
        }

        $model = $relation->first();

        if ( ! $model) return null;

        return $this->getRelation($model, implode('.', $relationNames));
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
