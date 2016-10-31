<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
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

}
