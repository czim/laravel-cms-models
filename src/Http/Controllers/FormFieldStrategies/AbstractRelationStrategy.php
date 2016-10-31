<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class AbstractRelationStrategy extends AbstractFormFieldStoreStrategy
{

    /**
     * Prepares the relation query builder for CMS retrieval.
     *
     * @param Relation|Builder $query
     * @return Builder
     */
    protected function prepareRelationQuery($query)
    {
        return $query->withoutGlobalScopes();
    }

}
