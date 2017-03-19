<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;
use Czim\CmsModels\Repositories\Criteria\ModelOrderStrategy;
use Czim\CmsModels\Strategies\Sort\NullLast;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\Repository\Contracts\ExtendedRepositoryInterface;
use Czim\Repository\Enums\CriteriaKey;

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
     * @param bool $update
     * @return $this
     */
    protected function checkActiveSort($update = true)
    {
        $request = request();

        if ($update && $request->exists('sort')) {

            $this->activeSort = $request->get('sort');

            if ($request->has('sortdir') && ! empty($request->get('sortdir'))) {
                $this->activeSortDescending = strtolower($request->get('sortdir')) === 'desc';
            } else {
                $this->activeSortDescending = null;
            }

            $this->markResetActivePage()
                 ->storeActiveSortInSession();

        } elseif ($this->getListMemory()->hasSortData()) {

            $this->retrieveActiveSortFromSession();
        }

        return $this;
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

        $this->getListMemory()->setSortData($this->activeSort, $direction);
    }

    /**
     * Retrieves the sort settings from the session and restores them as active.
     */
    protected function retrieveActiveSortFromSession()
    {
        $sessionSort = $this->getListMemory()->getSortData();

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
        $info = $this->getModelInformation();

        // Sort by orderable strategy if we should and can
        if (    $info->list->orderable
            &&  $sort == $info->list->getOrderableColumn()
            &&  ($orderableStrategy = $this->getOrderableSortStrategy())
        ) {
            return $orderableStrategy;
        }

        // Otherwise, attempt to sort by any other list column
        if ( ! isset($info->list->columns[$sort])) {
            return false;
        }

        $strategy = $info->list->columns[$sort]->sort_strategy;
        $source   = $info->list->columns[$sort]->source ?: $sort;

        return new ModelOrderStrategy($strategy, $source, $this->getActualSortDirection());
    }

    /**
     * Returns the order strategy to use
     *
     * @todo: the sort strategy should be configurable / orderable strategy determined
     *
     * @return false|ModelOrderStrategy
     */
    protected function getOrderableSortStrategy()
    {
        return new ModelOrderStrategy(
            NullLast::class,
            $this->getModelInformation()->list->getOrderableColumn(),
            $this->getActualSortDirection()
        );
    }

    /**
     * Applies active sorting to model repository.
     *
     * @return $this
     */
    protected function applySort()
    {
        $sort = $this->getActualSort();

        if ( ! $sort) return $this;

        $criteria = $this->getModelSortCriteria();

        if ( ! $criteria) return $this;

        $this->getModelRepository()->pushCriteria($criteria, CriteriaKey::ORDER);

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
