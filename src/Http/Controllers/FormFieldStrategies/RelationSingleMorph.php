<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

class RelationSingleMorph extends AbstractRelationStrategy
{
    // The separator symbol that splits the model class and model key parts of the value.
    const CLASS_AND_KEY_SEPARATOR = ':';


    /**
     * Returns field value based on list parent key data.
     *
     * Note that the handling of morph list parent record 'keys' should use
     * static::CLASS_AND_KEY_SEPARATOR for this to work as-is!
     *
     * @param string $key
     * @return mixed
     */
    public function valueForListParentKey($key)
    {
        return $key;
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        $relation = $this->resolveModelSource($model, $source);

        // Only MorphTo relation is expected
        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException("{$source} did not resolve to relation");
        }

        if ( ! ($relation instanceof MorphTo)) {
            throw new UnexpectedValueException(
                'Unexpected relation class ' . get_class($relation) . " for {$source}"
            );
        }

        // Retrieve the model if we can
        $class = $this->getModelClassFromValue($value);
        $key   = $this->getModelKeyFromValue($value);

        if ( ! $class || ! $key) {
            $relation->dissociate();
            return;
        }

        $model = $this->getModelByKey($class, $key);

        if ( ! $model) {
            $relation->dissociate();
            return;
        }

        $relation->associate($model);
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
        // Nothing to do, all handled in performStore
    }


    /**
     * Returns the model class part of a morph model/key combination value.
     *
     * @param string $value
     * @return string|null
     */
    protected function getModelClassFromValue($value)
    {
        if (null === $value) return null;

        $parts = explode(static::CLASS_AND_KEY_SEPARATOR, $value, 2);

        return $parts[0];
    }

    /**
     * Returns the model key part of a morph model/key combination value.
     *
     * @param string $value
     * @return mixed|null
     */
    protected function getModelKeyFromValue($value)
    {
        if (null === $value) return null;

        $parts = explode(static::CLASS_AND_KEY_SEPARATOR, $value, 2);

        if (count($parts) < 2) {
            throw new UnexpectedValueException("Morph model value is not formatted as 'class:key'.");
        }

        return $parts[1];
    }

    /**
     * Returns the value per relation for a given relation query builder.
     *
     * @param Builder|Relation $query
     * @return mixed|null
     */
    protected function getValueFromRelationQuery($query)
    {
        // Prevent looking up connected model when foreign keys are NULL to prevent query errors.

        /** @var MorphTo $query */
        $typeName = $query->getMorphType();
        $keyName  = $query->getForeignKey();

        if ( ! $this->model->{$typeName} || ! $this->model->{$keyName}) {
            return null;
        }


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

        return get_class($model)
             . static::CLASS_AND_KEY_SEPARATOR
             . $model->getKey();
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
