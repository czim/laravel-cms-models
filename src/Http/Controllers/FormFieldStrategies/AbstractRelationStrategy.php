<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use RuntimeException;
use UnexpectedValueException;

abstract class AbstractRelationStrategy extends AbstractFormFieldStoreStrategy
{

    /**
     * The parent model of the form field strategy.
     *
     * @var Model
     */
    protected $model;


    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        $this->model = $model;

        if ($this->isTranslated()) {

            $keys      = [];
            $localeKey = config('translatable.locale_key', 'locale');

            foreach ($model->translations as $translation) {
                /** @var Relation $relation */
                $relation = $translation->{$source}();

                $keys[ $translation->{$localeKey} ] = $this->getValueFromRelationQuery($relation);
            }

            return $keys;
        }

        $relation = $this->resolveModelSource($model, $source);

        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException(
                "{$source} did not resolve to a relation for " . get_class($this) . " on " . get_class($model)
            );
        }

        try {
            return $this->getValueFromRelationQuery($relation);

        } catch (\Exception $e) {

            throw new RuntimeException(
                "Exception retrieving relation values for source '{$source}' "
                . "for form field key '{$this->formFieldData->key}'",
                0,
                $e
            );
        }
    }

    /**
     * Returns the value per relation for a given relation query builder.
     *
     * @param Builder|Relation $query
     * @return mixed|null
     */
    abstract protected function getValueFromRelationQuery($query);

    /**
     * Prepares the relation query builder for CMS retrieval.
     *
     * Call from getValueFromRelationQuery if required.
     *
     * @param Relation|Builder $query
     * @return Builder
     */
    protected function prepareRelationQuery($query)
    {
        return $query->withoutGlobalScopes();
    }

    /**
     * @return ModelReferenceRepositoryInterface
     */
    protected function getModelReferenceRepository()
    {
        return app(ModelReferenceRepositoryInterface::class);
    }

    /**
     * Detaches models no longer to be related for *One and *Many relations.
     *
     * Note that all models are expected to be of a single class.
     *
     * @param Collection|Model[]|Model $models
     * @param Relation                 $relation
     */
    protected function detachRelatedModelsForOneOrMany($models, Relation $relation)
    {
        if ($models instanceof Model) {
            $models = new Collection([ $models ]);
        }

        if ( ! $this->allowDetachingOfOneOrMany() || ! count($models)) {
            return;
        }

        // If models that were previously attached but no longer should be
        // have nullable foreign keys, we can 'detach' them safely by dissociating
        // the reverse relation (if we know which relation that is, or nullifying the keys).

        // Get the foreign key(s) (id/type for morph, otherwise just the id)
        $foreignKeyNames = $this->getForeignKeyNamesForRelation($relation);

        $nullableKey = $this->explicitlyConfiguredForeignKeysNullable();
        if (null === $nullableKey) {
            $nullableKey = $this->hasNullableForeignKeys($relation, $models->first(), $foreignKeyNames);
        }

        if ($nullableKey) {
            // We can detach the models by setting their keys to null.
            foreach ($models as $model) {
                $this->setForeignKeysToNull($model, $relation, $foreignKeyNames);
            }

            return;
        }

        // If the related models do not have nullable keys, we cannot detach them
        // without deleting them. This must be explicitly configured to prevent accidental deletions.
        if ( ! $this->allowDeletionOfDetachedModels()) return;

        // We can delete the previously related models entirely
        foreach ($models as $model) {
            $model->delete();

            // todo: log?
        }
    }

    /**
     * Returns the foreign key names for a given relation class.
     *
     * For BelongsToMany, this returns the key to the local model first, the other model second.
     * For Morph relations, this will return the id/key and type column names.
     * For MorphToMany the morph keys are followed by the other foreign key.
     *
     * @param Relation $relation
     * @return string[]
     */
    protected function getForeignKeyNamesForRelation(Relation $relation)
    {
        if (    $relation instanceof BelongsTo
            ||  $relation instanceof HasOne
            ||  $relation instanceof HasMany
            ||  is_a($relation, '\\Znck\\Eloquent\\Relations\\BelongsToThrough', true)
        ) {
            return [ $relation->getForeignKey() ];
        }

        if ($relation instanceof BelongsToMany) {
            return [ $relation->getForeignKey(), $relation->getOtherKey() ];
        }

        if (    $relation instanceof MorphTo
            ||  $relation instanceof MorphOne
            ||  $relation instanceof MorphMany
        ) {
            return [ $relation->getForeignKey(), $relation->getMorphType() ];
        }

        if ($relation instanceof MorphToMany) {
            return [ $relation->getForeignKey(), $relation->getMorphType(), $relation->getOtherKey() ];
        }

        return [];
    }

    /**
     * Returns whether a relation's related model has nullable foreign keys.
     *
     * @param Relation      $relation
     * @param Model         $model     if the model with the keys should not be resolved, pass it in
     * @param string[]|null $keys      if already known, the foreign keys for the relation
     * @return bool
     */
    protected function hasNullableForeignKeys(Relation $relation, Model $model = null, array $keys = null)
    {
        if (null === $keys) {
            $keys = $this->getForeignKeyNamesForRelation($relation);
        }

        if ( ! count($keys)) return false;

        // Determine the model on which the foreign key is stored

        if (null === $model) {

            if (    $relation instanceof BelongsTo
                ||  $relation instanceof MorphTo
                ||  is_a($relation, '\\Znck\\Eloquent\\Relations\\BelongsToThrough', true)
            ) {
                $model = $this->model;

            } elseif (  $relation instanceof HasOne
                    ||  $relation instanceof HasMany
                    ||  $relation instanceof BelongsToMany
            ) {
                $model = $relation->getRelated();
            }
        }

        if ( ! $model) {
            throw new RuntimeException('No model could be determined for nullable foreign key check');
        }

        $info = $this->getModelInformation($model);

        // We can use the information to determine whether the model has nullable foreign key
        if ($info) {

            // For all relations, the first key value must be checked
            $attribute = array_get($info->attributes, $this->normalizeForeignKey(head($keys)));
            $nullable  = $attribute ? $attribute->nullable : false;

            // For morph relation types, we must also check the type column
            if (    $nullable
                &&  count($keys) > 1
                &&  (   $relation instanceof MorphTo
                    ||  $relation instanceof MorphOne
                    ||  $relation instanceof MorphMany
                    )
            ) {
                $attribute = array_get($info->attributes, $this->normalizeForeignKey(array_values($keys)[1]));
                $nullable  = $attribute ? $attribute->nullable : false;
            }

            return $nullable;
        }

        // We must somehow determine whether the foreign key is nullable on this
        // model without having information about it.

        // todo: for now, assume false, use the 'nullable_keys' option to configure this instead

        return false;
    }

    /**
     * Sets the foreign keys to null on a given model.
     *
     * @param Model    $model
     * @param Relation $relation
     * @param string[] $keys
     */
    protected function setForeignKeysToNull(Model $model, Relation $relation, array $keys)
    {
        if ( ! count($keys)) return;

        $key           = $this->normalizeForeignKey(head($keys));
        $model->{$key} = null;

        if (    count($keys) > 1
            &&  (   $relation instanceof MorphTo
                ||  $relation instanceof MorphOne
                ||  $relation instanceof MorphMany
            )
        ) {
            $key           = $this->normalizeForeignKey(array_values($keys)[1]);
            $model->{$key} = null;
        }

        $model->save();
    }

    /**
     * Returns whether the relation's foreign keys were configured nullable.
     *
     * @return bool|null    null if the nullabillity of the keys was not configured
     */
    protected function explicitlyConfiguredForeignKeysNullable()
    {
        $nullable = array_get($this->formFieldData->options(), 'nullable_key');

        if (null === $nullable) return null;

        return (bool) $nullable;
    }

    /**
     * Returns whether detachment of *One or *Many related models not in the updated value is allowed.
     *
     * This is allowed by default, unless specified to be disallowed.
     *
     * @return bool
     */
    protected function allowDetachingOfOneOrMany()
    {
        return (bool) array_get($this->formFieldData->options(), 'detach', true);
    }

    /**
     * Returns whether deletion has been explicitly allowed.
     *
     * @return bool
     */
    protected function allowDeletionOfDetachedModels()
    {
        return (bool) array_get($this->formFieldData->options(), 'delete');
    }

    /**
     * Normalizes foreign keys, to strip their tables if they are fully qualified.
     *
     * @param string $name
     * @return string
     */
    protected function normalizeForeignKey($name)
    {
        $firstKeyParts = explode('.', $name);

        return array_pop($firstKeyParts);
    }

}
