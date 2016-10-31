<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelationSingleKey extends AbstractRelationStrategy
{

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

}
