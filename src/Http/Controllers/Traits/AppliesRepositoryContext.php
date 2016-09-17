<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Repositories\Criteria\ContextStrategy;
use Czim\CmsModels\Repositories\Criteria\DisableGlobalScopes;
use Czim\CmsModels\Support\Data\ModelInformation;

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
             ->disableGlobalScopes();

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
     * @return ModelInformationInterface|ModelInformation
     */
    abstract protected function getModelInformation();

    /**
     * @return ModelRepositoryInterface
     */
    abstract protected function getModelRepository();

}
