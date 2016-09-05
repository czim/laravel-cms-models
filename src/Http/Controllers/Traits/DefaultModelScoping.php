<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
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
     * @return $this
     */
    protected function applyScope(ExtendedRepositoryInterface $repository)
    {
        $repository->clearScopes();

        if ($this->activeScope) {
            $repository->addScope($this->activeScope);
        }

        return $this;
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
        session()->put($this->getScopeSessionKey(), $this->activeScope);
    }

    /**
     * Retrieves the scope from the session and restores them as active.
     */
    protected function retrieveActiveScopeFromSession()
    {
        $this->activeScope = session()->get($this->getScopeSessionKey());
    }

    /**
     * @return string
     */
    protected function getScopeSessionKey()
    {
        return $this->getCore()->config('session.prefix')
             . 'model:' . $this->getModuleKey()
             . ':scope';
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function isValidScopeKey($key)
    {
        $info = $this->getModelInformation();

        if ($info->list->disable_scopes) {
            return false;
        }

        return in_array($key, array_keys($info->list->scopes));
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
     * @param bool $reset
     * @return $this
     */
    abstract protected function markResetActivePage($reset = true);
}
