<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Repositories\Criteria\ModelOrderStrategy;
use Czim\CmsModels\Support\Data\ModelInformation;

trait DefaultModelSorting
{

    /**
     * @var string|null
     */
    protected $activeSort;

    /**
     * @var bool|null
     */
    protected $activeSortDescending;


    /**
     * Checks and sets the active sort settings.
     *
     * @return array
     */
    protected function checkActiveSort()
    {
        $request = request();

        if ($request->has('sort')) {

            $this->activeSort = $request->get('sort');

            if ($request->has('sortdir') && ! empty($request->get('sortdir'))) {
                $this->activeSortDescending = strtolower($request->get('sortdir')) === 'desc';
            } else {
                $this->activeSortDescending = null;
            }

            $this->markResetActivePage()
                 ->storeActiveSortInSession();

        } elseif (session()->has($this->getSortSessionKey())) {

            $this->retrieveActiveSortFromSession();
        }
    }

    /**
     * Stores the currently active sort settings for the session.
     */
    protected function storeActiveSortInSession()
    {
        if (null !== $this->activeSortDescending) {
            $direction = $this->activeSortDescending ? 'desc' : 'asc';
        } else {
            $direction = null;
        }

        session()->put($this->getSortSessionKey(), [
            'column'    => $this->activeSort,
            'direction' => $direction,
        ]);
    }

    /**
     * Retrieves the sort settings from the session and restores them as active.
     */
    protected function retrieveActiveSortFromSession()
    {
        $sessionSort = session()->get($this->getSortSessionKey());

        if ( ! is_array($sessionSort)) return;

        $this->activeSort = array_get($sessionSort, 'column');

        $direction = array_get($sessionSort, 'direction');

        if (null === $direction) {
            $this->activeSortDescending = null;
        } else {
            $this->activeSortDescending = $direction === 'desc';
        }
    }

    /**
     * @return string
     */
    protected function getSortSessionKey()
    {
        return $this->getCore()->config('session.prefix')
             . 'model:' . $this->getModuleKey()
             . ':sort';
    }

    /**
     * Returns actual sorting column/source to use.
     *
     * @return null|string
     */
    protected function getActualSort()
    {
        if ( ! $this->activeSort) {
            return $this->getDefaultSort();
        }

        return $this->activeSort;
    }

    /**
     * Returns the sort direction that is actually active, determined by
     * the specified sort direction with a fallback to the default.
     *
     * @param string|null $column
     * @return string   asc|desc
     */
    protected function getActualSortDirection($column = null)
    {
        $column = $column ?: $this->activeSort;

        if (null !== $this->activeSortDescending) {
            return $this->activeSortDescending ? 'desc' : 'asc';
        }

        if ( ! isset($this->getModelInformation()->list->columns[$column])) {

            if (null === $column) {
                return $this->getActualSortDirection($this->getDefaultSort());
            }

            return 'asc';
        }

        $direction = $this->getModelInformation()->list->columns[$column]->sort_direction;

        return strtolower($direction) === 'desc' ? 'desc' : 'asc';
    }

    /**
     * Returns the default sorting column/source for the current model.
     *
     * @return string
     */
    protected function getDefaultSort()
    {
        return $this->getModelInformation()->list->default_sort;
    }

    /**
     * Returns the active model sorting criteria to apply to the model repository.
     *
     * @return ModelOrderStrategy|false
     */
    protected function getModelSortCriteria()
    {
        $sort = $this->getActualSort();

        if ( ! isset($this->getModelInformation()->list->columns[$sort])) {
            return false;
        }

        $strategy = $this->getModelInformation()->list->columns[$sort]->sort_strategy;
        $source   = $this->getModelInformation()->list->columns[$sort]->source ?: $sort;

        return new ModelOrderStrategy($strategy, $source, $this->getActualSortDirection());
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
