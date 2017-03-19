<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Repositories\Criteria\ContextStrategy;
use Czim\CmsModels\Repositories\Criteria\DisableGlobalScopes;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\Repository\Criteria\Common\WithRelations;
use Czim\Repository\Enums\CriteriaKey;

trait AppliesRepositoryContext
{

    /**
     * Applies criteria-based repository context strategies.
     *
     * If repository or information are not given, they are read from standard
     * methods: getModelRepository() and getModelInformation respectively.
     *
     * @param ModelRepositoryInterface|null                   $repository
     * @param ModelInformationInterface|ModelInformation|null $information
     * @return $this
     */
    protected function applyRepositoryContext(
        ModelRepositoryInterface $repository = null,
        ModelInformationInterface $information = null
    ) {
        $repository  = $repository  ?: call_user_func([ $this, 'getModelRepository' ]);
        $information = $information ?: call_user_func([ $this, 'getModelInformation' ]);

        $this->prepareRepositoryContextStrategy($repository, $information)
             ->disableGlobalScopes($repository, $information)
             ->applyIncludesToRepository($repository, $information);

        return $this;
    }

    /**
     * @param ModelRepositoryInterface                   $repository
     * @param ModelInformationInterface|ModelInformation $information
     * @return $this
     */
    protected function prepareRepositoryContextStrategy(
        ModelRepositoryInterface $repository,
        ModelInformationInterface $information
    ) {
        if ($strategy = $information->meta->repository_strategy) {

            $repository->pushCriteria(
                new ContextStrategy($strategy, $information->meta->repository_strategy_parameters ?: [])
            );
        }

        return $this;
    }

    /**
     * @param ModelRepositoryInterface                   $repository
     * @param ModelInformationInterface|ModelInformation $information
     * @return $this
     */
    protected function disableGlobalScopes(
        ModelRepositoryInterface $repository,
        ModelInformationInterface $information
    ) {
        if ($disableScopes = $information->meta->disable_global_scopes) {

            $repository->pushCriteria(
                new DisableGlobalScopes($disableScopes)
            );
        }

        return $this;
    }

    /**
     * @param ModelRepositoryInterface                   $repository
     * @param ModelInformationInterface|ModelInformation $information
     * @return $this
     */
    protected function applyIncludesToRepository(
        ModelRepositoryInterface $repository,
        ModelInformationInterface $information
    ) {
        $includes = array_get($information->includes, 'default', []);

        if (count($includes)) {

            $repository->pushCriteria(
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

}
