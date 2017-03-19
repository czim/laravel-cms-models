<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

/**
 * Class RelationPivotOrdered
 *
 * This strategy is only compatible with BelongsTo relations.
 */
class RelationPivotOrdered extends AbstractRelationStrategy
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
        if ( ! ($query instanceof BelongsToMany)) {
            throw new UnexpectedValueException("Query must be BelongsToMany Relation instance for " . get_class($this));
        }

        /** @var BelongsToMany $query */

        $relatedModel = $query->getRelated();
        $keyName      = $relatedModel->getKeyName();
        $table        = $relatedModel->getTable();
        $pivotTable   = $query->getTable();
        $orderColumn  = $this->getPivotOrderColumn();

        $query = $this->prepareRelationQuery(
            $query
                ->withPivot([ $orderColumn ])
                ->orderBy($pivotTable . '.' . $orderColumn)
        );

        // Get only the relevant columns: key and pivot
        return $query->pluck($pivotTable . '.' . $orderColumn, $table . '.' . $keyName);
    }

    /**
     * Returns field value based on list parent key data.
     *
     * @param string $key
     * @return mixed
     */
    public function valueForListParentKey($key)
    {
        return [ $key ];
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        // Nothing needs to be done for BelongsTo relations
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

        if ( ! ($relation instanceof BelongsToMany)) {
            throw new UnexpectedValueException("{$source} did not resolve to BelongsToMany relation");
        }

        $relation->sync(
            $this->convertValueToSyncFormat($value)
        );
    }

    /**
     * Converts submitted value array to sync() format.
     *
     * @param array|null $value
     * @return array    associative, keys are related model ids, values update values
     */
    protected function convertValueToSyncFormat($value)
    {
        if (null === $value) {
            return [];
        }

        $orderColumn = $this->getPivotOrderColumn();

        return array_map(
            function ($position) use ($orderColumn) {
                return [ $orderColumn => $position ];
            },
            $value
        );
    }

    /**
     * Returns the unqualified ordering column name on the pivot table.
     *
     * @return string
     */
    protected function getPivotOrderColumn()
    {
        return array_get($this->formFieldData->options, 'position_column', 'position');
    }

}
