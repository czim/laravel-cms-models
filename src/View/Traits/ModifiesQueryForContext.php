<?php
namespace Czim\CmsModels\View\Traits;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Helps to apply the correct CMS repository context to any query on a model
 * (or a relation to it).
 */
trait ModifiesQueryForContext
{
    use ResolvesRepositoryContextStrategy;

    /**
     * Modifies the relation query according to the model's CMS information.
     *
     * @param Model            $model
     * @param Builder|Relation $query
     * @return Builder
     */
    protected function modifyRelationQueryForContext(Model $model, $query)
    {
        /** @var Relation $relation */
        $information = $this->getInformationRepository()->getByModel($model);

        if ( ! $information) {
            return $query;
        }

        // Deal with global scopes, if any
        $disableScopes = $information->meta->disable_global_scopes;

        if (true === $disableScopes || is_array($disableScopes)) {
            $query->withoutGlobalScopes(
                true === $disableScopes ? null : $disableScopes
            );
        }

        // Apply repository context, if any
        $strategy = $information->meta->repository_strategy;

        if ($strategy) {
            $strategy = $this->resolveContextStrategy($strategy);

            if ($strategy) {
                $query = $strategy->apply($query, $information);
            }
        }

        return $query;
    }


    /**
     * @return ModelInformationRepositoryInterface
     */
    abstract protected function getInformationRepository();

}
