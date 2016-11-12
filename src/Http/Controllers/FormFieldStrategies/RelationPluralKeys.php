<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use UnexpectedValueException;

/**
 * Class RelationPluralKeys
 *
 * This strategy is not compatible with polymorphic relations.
 */
class RelationPluralKeys extends AbstractRelationStrategy
{

    /**
     * Returns the value per relation for a given relation query builder.
     *
     * @param Builder|Relation $query
     * @return mixed|null
     */
    protected function getValueFromRelationQuery($query)
    {
        // Query must be a relation in this case, since we need the related model
        if ( ! ($query instanceof Relation)) {
            throw new UnexpectedValueException("Query must be Relation instance for " . get_class($this));
        }

        $keyName = $query->getRelated()->getKeyName();

        $query = $this->prepareRelationQuery($query);

        return $query->pluck($keyName);
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        // Nothing needs to be done for plural keys strategy
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

        // Any singular relations are unexpected
        if (    $relation instanceof BelongsTo
            ||  $relation instanceof HasOne
            ||  $relation instanceof MorphOne
        ) {
            throw new UnexpectedValueException("{$source} is a single relation, expecting plural");
        }

        if (null === $value) return;

        // Should not be used, since this is for singular relations..
        if ($relation instanceof BelongsToMany) {
            $relation->sync([ $value ]);
            return;
        }

        // For HasMany, the related models must be found,
        // and then they should be saved on the relation.
        $relatedModels = $this->getModelsByKey($relation->getRelated(), $value);

        // todo: this does not sync (detach) models .. should we?
        if ( ! count($relatedModels)) return;

        if ($relation instanceof HasOne || $relation instanceof HasMany) {
            foreach ($relatedModels as $relatedModel) {
                $relation->save($relatedModel);
            }
            return;
        }

        throw new UnexpectedValueException(
            'Unexpected relation class ' . get_class($relation) . " for {$source}"
        );
    }

    /**
     * Finds (to be related) models by their keys.
     *
     * @param string|Model    $model
     * @param array|Arrayable $keys
     * @return Collection|Model[]
     */
    protected function getModelsByKey($model, $keys)
    {
        if (is_string($model)) {
            $model = new $model;
        }

        /** @var Model $model */
        return $model->withoutGlobalScopes()
            ->whereIn($model->getKeyName(), $keys)
            ->get()
            ->keyBy($model->getKeyName());
    }

}
