<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;

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
     * @param bool $update
     * @return $this
     */
    protected function checkActivePage($update = true)
    {
        $request = request();

        $pageSetByRequest     = $request->has('page') || $this->resetActivePage;
        $pageSizeSetByRequest = $request->filled('pagesize');

        if ($update && $pageSetByRequest) {

            $this->activePage = $request->get('page') ? (int) $request->get('page') : null;

        } elseif ($this->getListMemory()->hasPage()) {

            $this->retrieveActivePageFromSession();
        }

        if ($update && $pageSizeSetByRequest) {

            $this->activePageSize = $request->get('pagesize') ? (int) $request->get('pagesize') : null;
            $this->activePage     = null;

        } elseif ($this->getListMemory()->hasPageSize()) {

            $this->retrieveActivePageSizeFromSession();
        }

        if ($update && ($pageSetByRequest || $pageSizeSetByRequest)) {
            $this->storeActivePageValuesInSession();
        }

        return $this;
    }

    /**
     * Stores the currently active page settings for the session.
     */
    protected function storeActivePageValuesInSession()
    {
        $this->getListMemory()->setPage($this->activePage);
        $this->getListMemory()->setPageSize($this->activePageSize);
    }

    /**
     * Retrieves the sort settions from the session and restores them as active.
     */
    protected function retrieveActivePageFromSession()
    {
        $this->activePage = $this->getListMemory()->getPage();
    }

    /**
     * Retrieves the sort settions from the session and restores them as active.
     */
    protected function retrieveActivePageSizeFromSession()
    {
        $this->activePageSize = $this->getListMemory()->getPageSize();
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
            ?: $this->getModelPageSize()
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
     * Returns model defined default page size.
     *
     * @return int
     */
    protected function getModelPageSize()
    {
        $pageSize = $this->getModelInformation()->list->page_size;

        if (is_array($pageSize)) {
            return (int) head($pageSize);
        }

        return (int) $pageSize;
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
        $pageSizes = $this->getModelInformation()->list->page_size;

        if (is_array($pageSizes)) {
            return $pageSizes;
        }

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

    /**
     * @return ModelListMemoryInterface
     */
    abstract protected function getListMemory();

}
