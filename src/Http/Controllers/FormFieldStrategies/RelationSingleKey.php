<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

class RelationSingleKey extends AbstractRelationStrategy
{

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        $relation = $this->resolveModelSource($model, $source);

        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException("{$source} did not resolve to relation");
        }

        // Only belongs to should be saved before the model itself is created.
        if ( ! ($relation instanceof BelongsTo)) return;

        if ( ! $value) {
            $relation->dissociate();
            return;
        }

         $relation->associate($value);
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function performStoreAfter(Model $model, $source, $value)
    {
        $relation = $this->resolveModelSource($model, $source);

        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException("{$source} did not resolve to relation");
        }

        // Belongs to are handled before the model is saved
        if ($relation instanceof BelongsTo) return;

        // Should not be used, since this is for singular relations..
        if ($relation instanceof BelongsToMany) {
            $relation->sync([ $value ]);
            return;
        }

        if ( ! $value) return;

        // For HasOne (and HasMany), the related model must be found,
        // and then it should be saved on the relation.
        $relatedModel = $this->getModelByKey($relation->getRelated(), $value);

        if ( ! $relatedModel) return;

        if (    $relation instanceof HasOne
            ||  $relation instanceof HasMany
            ||  $relation instanceof MorphOne
            ||  $relation instanceof MorphMany
        ) {
            $relation->save($relatedModel);
            return;
        }

        throw new UnexpectedValueException(
            'Unexpected relation class ' . get_class($relation) . " for {$source}"
        );
    }

    /**
     * Returns the value per relation for a given relation query builder.
     *
     * @param Builder|Relation $query
     * @return mixed|null
     */
    protected function getValueFromRelationQuery($query)
    {
        $query = $this->prepareRelationQuery($query);

        return $this->getValueFromModel(
            $query->first()
        );
    }

    /**
     * Returns the value for a single related model.
     *
     * @param Model|null $model
     * @return mixed|null
     */
    protected function getValueFromModel($model)
    {
        if ( ! ($model instanceof Model)) {
            return null;
        }

        return $model->getKey();
    }

    /**
     * Finds a (to be related) model by its key.
     *
     * @param string|Model $model
     * @param string       $key
     * @return Model|null
     */
    protected function getModelByKey($model, $key)
    {
        if (is_string($model)) {
            $model = new $model;
        }

        /** @var Model $model */
        return $model->withoutGlobalScopes()->find($key);
    }

}
