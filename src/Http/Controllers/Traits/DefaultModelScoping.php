<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\Repository\Contracts\ExtendedRepositoryInterface;

trait DefaultModelScoping
{

    /**
     * The currently active scope
     *
     * @var null|string
     */
    protected $activeScope;


    /**
     * Checks for the active scope.
     *
     * @return $this
     */
    protected function checkScope()
    {
        $request = request();

        if ($request->has('scope') || in_array('scope', array_keys($request->query()))) {

            $this->activeScope = $request->get('scope');

            $this->markResetActivePage();
            $this->storeActiveScopeInSession();
        } else {
            $this->retrieveActiveScopeFromSession();
        }

        // Check if active is valid, reset it othwerwise
        if ($this->activeScope && ! $this->isValidScopeKey($this->activeScope)) {
            $this->activeScope = null;
            $this->storeActiveScopeInSession();
        }

        return $this;
    }

    /**
     * Applies the currently active scope to a repository.
     *
     * @param ExtendedRepositoryInterface $repository
     * @param null|string                 $scope        active if not given
     * @return $this
     */
    protected function applyScope(ExtendedRepositoryInterface $repository, $scope = null)
    {
        $scope = (null === $scope) ? $this->activeScope : $scope;

        $repository->clearScopes();

        $info = $this->getModelInformation();

        if ($scope) {

            $method = $info->list->scopes[ $scope ]->method ?: $scope;

            $repository->addScope($method);
        }

        return $this;
    }

    /**
     * Returns total amount of matches for each available scope.
     *
     * @return int[]    assoc, keyed by scope key
     */
    protected function getScopeCounts()
    {
        if ( ! $this->areScopesEnabled()) {
            return [];
        }

        $info = $this->getModelInformation();

        if ( ! $info->list->scopes || ! count($info->list->scopes)) {
            return [];
        }

        $counts = [];

        $repository = $this->getModelRepository();

        foreach (array_keys($info->list->scopes) as $key) {

            $this->applyScope($repository, $key);

            $counts[ $key ] = $repository->count();
        }

        return $counts;
    }

    /**
     * @return null|string
     */
    protected function getActiveScope()
    {
        return $this->activeScope;
    }

    /**
     * Stores the currently active scope in the session.
     */
    protected function storeActiveScopeInSession()
    {
        $this->getListMemory()->setScope($this->activeScope);
    }

    /**
     * Retrieves the scope from the session and restores them as active.
     */
    protected function retrieveActiveScopeFromSession()
    {
        $this->activeScope = $this->getListMemory()->getScope();
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function isValidScopeKey($key)
    {
        if ( ! $this->areScopesEnabled()) {
            return false;
        }

        return in_array($key, array_keys($this->getModelInformation()->list->scopes));
    }

    /**
     * Returns whether scopes are enabled at all.
     *
     * @return bool
     */
    protected function areScopesEnabled()
    {
        $info = $this->getModelInformation();

        return ! $info->list->disable_scopes;
    }


    /**
     * @return CoreInterface
     */
    abstract protected function getCore();

    /**
     * @return string
     */
    abstract protected function getModuleKey();

    /**
     * @return ModelInformationInterface|ModelInformation|null
     */
    abstract protected function getModelInformation();

    /**
     * @return ModelRepositoryInterface|ExtendedRepositoryInterface
     */
    abstract protected function getModelRepository();

    /**
     * @param bool $reset
     * @return $this
     */
    abstract protected function markResetActivePage($reset = true);

    /**
     * @return ModelListMemoryInterface
     */
    abstract protected function getListMemory();

}
