<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Repositories\Criteria\ContextStrategy;
use Czim\CmsModels\Repositories\Criteria\DisableGlobalScopes;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\Repository\Criteria\Common\WithRelations;
use Czim\Repository\Enums\CriteriaKey;

trait AppliesRepositoryContext
{

    /**
     * Applies criteria-based repository context strategies.
     *
     * @return $this
     */
    protected function applyRepositoryContext()
    {
        $this->prepareRepositoryContextStrategy()
             ->disableGlobalScopes()
             ->applyIncludesToRepository();

        return $this;
    }

    /**
     * @return $this
     */
    protected function prepareRepositoryContextStrategy()
    {
        if ($strategy = $this->getModelInformation()->meta->repository_strategy) {

            $this->getModelRepository()->pushCriteria(
                new ContextStrategy($strategy, $this->getModelInformation())
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function disableGlobalScopes()
    {
        if ($disableScopes = $this->getModelInformation()->meta->disable_global_scopes) {

            $this->getModelRepository()->pushCriteria(
                new DisableGlobalScopes($disableScopes)
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function applyIncludesToRepository()
    {
        $information = $this->getModelInformation();

        $includes = array_get($information->includes, 'default', []);

        if (count($includes)) {

            $this->getModelRepository()->pushCriteria(
                new WithRelations(
                    $this->normalizeIncludesArray($includes)
                ),
                CriteriaKey::WITH
            );
        }

        return $this;
    }

    /**
     * Normalizes includes array, preparing them for the repository.
     *
     * @todo add strategy handling for key/value pairs.
     *
     * @param array $includes
     * @return array
     */
    protected function normalizeIncludesArray(array $includes)
    {
        return $includes;
    }


    /**
     * @return ModelInformationInterface|ModelInformation
     */
    abstract protected function getModelInformation();

    /**
     * @return ModelRepositoryInterface
     */
    abstract protected function getModelRepository();

}
