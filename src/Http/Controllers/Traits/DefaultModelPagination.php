<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelInformation;

trait DefaultModelPagination
{

    /**
     * @var int|null
     */
    protected $activePage;

    /**
     * @var int|null
     */
    protected $activePageSize;

    /**
     * Whether other updates or settings should force a page reset.
     *
     * @var bool
     */
    protected $resetActivePage = false;


    /**
     * Checks and sets the active sort settings.
     *
     * @return $this
     */
    protected function checkActivePage()
    {
        $request = request();

        $pageSetByRequest     = $request->has('page') || $this->resetActivePage;
        $pageSizeSetByRequest = $request->has('pagesize');

        if ($pageSetByRequest) {

            $this->activePage = $request->get('page') ? (int) $request->get('page') : null;

        } elseif (session()->has($this->getPageSessionKey())) {

            $this->retrieveActivePageFromSession();
        }

        if ($pageSizeSetByRequest) {

            $this->activePageSize = $request->get('pagesize') ? (int) $request->get('pagesize') : null;
            $this->activePage     = null;

        } elseif (session()->has($this->getPageSizeSessionKey())) {

            $this->retrieveActivePageSzeFromSession();
        }

        if ($pageSetByRequest || $pageSizeSetByRequest) {
            $this->storeActivePageValuesInSession();
        }

        return $this;
    }

    /**
     * Stores the currently active page settings for the session.
     */
    protected function storeActivePageValuesInSession()
    {
        session()->put($this->getPageSessionKey(), $this->activePage);
        session()->put($this->getPageSizeSessionKey(), $this->activePageSize);
    }

    /**
     * Retrieves the sort settions from the session and restores them as active.
     */
    protected function retrieveActivePageFromSession()
    {
        $this->activePage = session()->get($this->getPageSessionKey());
    }

    /**
     * Retrieves the sort settions from the session and restores them as active.
     */
    protected function retrieveActivePageSzeFromSession()
    {
        $this->activePageSize = session()->get($this->getPageSizeSessionKey());
    }

    /**
     * @return string
     */
    protected function getPageSessionKey()
    {
        return $this->getCore()->config('session.prefix')
             . 'model:' . $this->getModuleKey()
             . ':page';
    }

    /**
     * @return string
     */
    protected function getPageSizeSessionKey()
    {
        return $this->getCore()->config('session.prefix')
             . 'model:' . $this->getModuleKey()
             . ':pagesize';
    }

    /**
     * Returns actual page number to use.
     *
     * @return null|string
     */
    protected function getActualPage()
    {
        if (null === $this->activePage) {
            return $this->getDefaultPage();
        }

        return $this->activePage;
    }

    /**
     * Returns actual page size to use.
     *
     * @return int
     */
    protected function getActualPageSize()
    {
        return $this->activePageSize
            ?: $this->getModelInformation()->list->page_size
            ?: $this->getDefaultPageSize();
    }

    /**
     * Returns the default page value.
     *
     * @return string
     */
    protected function getDefaultPage()
    {
        return null;
    }

    /**
     * Returns the default page size value.
     *
     * @return int
     */
    protected function getDefaultPageSize()
    {
        return config('cms-models.strategies.list.page-size', 25);
    }

    /**
     * Returns the page size options that users can choose from.
     *
     * @return int[]|false
     */
    protected function getPageSizeOptions()
    {
        return config('cms-models.strategies.list.page-size-options', false);
    }

    /**
     * @param bool $reset
     * @return $this
     */
    protected function markResetActivePage($reset = true)
    {
        $this->resetActivePage = (bool) $reset;

        return $this;
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

}
