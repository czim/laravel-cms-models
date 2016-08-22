<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelInformation;

trait DefaultModelFiltering
{

    /**
     * The current filter settings
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @return array
     */
    protected function getActiveFilters()
    {
        return $this->filters;
    }

    /**
     * Checks and loads filters from the session.
     *
     * @return $this
     */
    protected function checkFilters()
    {
        if (session()->has($this->getFilterSessionKey())) {
            $this->retrieveFiltersFromSession();
        }

        return $this;
    }

    /**
     * Checks and sets the active sort settings.
     *
     * @return $this
     */
    protected function updateFilters()
    {
        $request = request();

        if ($request->has('_clear')) {
            $this->filters = [];
            $resetPage = ! empty($this->filters);
        } else {
            $this->filters = $request->get('filter', []);
            $resetPage = true;
        }

        $this->storeFiltersInSession();

        if ($resetPage) {
            $this->markResetActivePage();
        }

        return $this;
    }

    /**
     * Stores the currently set filters in the session.
     */
    protected function storeFiltersInSession()
    {
        if (empty($this->filters)) {
            session()->forget($this->getFilterSessionKey());
            return;
        }

        session()->put($this->getFilterSessionKey(), $this->filters);
    }

    /**
     * Retrieves the filters from the session and restores them.
     */
    protected function retrieveFiltersFromSession()
    {
        $this->filters = session()->get($this->getFilterSessionKey(), []);
    }

    /**
     * @return string
     */
    protected function getFilterSessionKey()
    {
        return $this->getCore()->config('session.prefix')
             . 'model:' . $this->getModuleKey()
             . ':filters';
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
