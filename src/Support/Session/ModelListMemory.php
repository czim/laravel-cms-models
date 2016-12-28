<?php
namespace Czim\CmsModels\Support\Session;

use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;

class ModelListMemory implements ModelListMemoryInterface
{
    const TYPE_FILTERS  = 'filters';
    const TYPE_SORT     = 'sort';
    const TYPE_PAGE     = 'page';
    const TYPE_PAGESIZE = 'pagesize';
    const TYPE_SCOPE    = 'scope';
    const TYPE_PARENT   = 'parent';

    /**
     * Signifies default parent filter has been disabled.
     *
     * @var string
     */
    const PARENT_DISABLE = '__DISABLED__';

    /**
     * The main context for retrieving list parameters.
     *
     * @var string|null
     */
    protected $context;

    /**
     * An optional secondary key for retrieving contextual list parameters.
     *
     * @var string|null
     */
    protected $contextSub;


    /**
     * @return null|string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setContext($key)
    {
        $this->context = $key;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubContext()
    {
        return $this->contextSub;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setSubContext($key)
    {
        $this->contextSub = $key;

        return $this;
    }


    /**
     * Returns whether filters are set for the current context.
     *
     * @return bool
     */
    public function hasFilters()
    {
        return session()->has($this->getSessionKey(static::TYPE_FILTERS));
    }

    /**
     * Returns filters set for current context.
     *
     * @return array
     */
    public function getFilters()
    {
        return session()->get($this->getSessionKey(static::TYPE_FILTERS), []);
    }

    /**
     * Sets filter data for the current context.
     *
     * @param array $filters
     * @return $this
     */
    public function setFilters(array $filters)
    {
        if (empty($filters)) {
            return $this->clearFilters();
        }

        session()->set($this->getSessionKey(static::TYPE_FILTERS), $filters);

        return $this;
    }

    /**
     * Clears all current filters for the current context.
     *
     * @return $this
     */
    public function clearFilters()
    {
        session()->forget($this->getSessionKey(static::TYPE_FILTERS));

        return $this;
    }

    /**
     * Returns whether sortData are set for the current context.
     *
     * @return bool
     */
    public function hasSortData()
    {
        return session()->has($this->getSessionKey(static::TYPE_SORT));
    }

    /**
     * Returns sortData set for current context.
     *
     * @return array    assoc, 'column', 'direction'
     */
    public function getSortData()
    {
        return session()->get($this->getSessionKey(static::TYPE_SORT), []);
    }

    /**
     * Sets filter data for the current context.
     *
     * @param string      $column
     * @param null|string $direction
     * @return $this
     */
    public function setSortData($column, $direction = null)
    {
        if (null === $column) {
            return $this->clearSortData();
        }

        session()->set($this->getSessionKey(static::TYPE_SORT), [
            'column'    => $column,
            'direction' => $direction,
        ]);

        return $this;
    }

    /**
     * Clears all current sortData for the current context.
     *
     * @return $this
     */
    public function clearSortData()
    {
        session()->forget($this->getSessionKey(static::TYPE_SORT));

        return $this;
    }

    /**
     * Returns whether an active page is set for the current context.
     *
     * @return bool
     */
    public function hasPage()
    {
        return session()->has($this->getSessionKey(static::TYPE_PAGE));
    }

    /**
     * Returns active page for current context.
     *
     * @return array
     */
    public function getPage()
    {
        return session()->get($this->getSessionKey(static::TYPE_PAGE), []);
    }

    /**
     * Sets active page for the current context.
     *
     * @param int $page
     * @return $this
     */
    public function setPage($page)
    {
        if (empty($page)) {
            return $this->clearPage();
        }

        session()->set($this->getSessionKey(static::TYPE_PAGE), $page);

        return $this;
    }

    /**
     * Clears active page for the current context.
     *
     * @return $this
     */
    public function clearPage()
    {
        session()->forget($this->getSessionKey(static::TYPE_PAGE));

        return $this;
    }

    /**
     * Returns whether an active page size is set for the current context.
     *
     * @return bool
     */
    public function hasPageSize()
    {
        return session()->has($this->getSessionKey(static::TYPE_PAGESIZE));
    }

    /**
     * Returns active page size for current context.
     *
     * @return array
     */
    public function getPageSize()
    {
        return session()->get($this->getSessionKey(static::TYPE_PAGESIZE), []);
    }

    /**
     * Sets active page size for the current context.
     *
     * @param int $size
     * @return $this
     */
    public function setPageSize($size)
    {
        if ( ! $size) {
            return $this->clearPageSize();
        }

        session()->set($this->getSessionKey(static::TYPE_PAGESIZE), $size);

        return $this;
    }

    /**
     * Clears active page size for the current context.
     *
     * @return $this
     */
    public function clearPageSize()
    {
        session()->forget($this->getSessionKey(static::TYPE_PAGESIZE));

        return $this;
    }

    /**
     * Returns whether an active scope is set for the current context.
     *
     * @return bool
     */
    public function hasScope()
    {
        return session()->has($this->getSessionKey(static::TYPE_PAGE));
    }

    /**
     * Returns active scope for current context.
     *
     * @return array
     */
    public function getScope()
    {
        return session()->get($this->getSessionKey(static::TYPE_PAGE), []);
    }

    /**
     * Sets active scope for the current context.
     *
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        if (empty($scope)) {
            return $this->clearScope();
        }

        session()->set($this->getSessionKey(static::TYPE_PAGE), $scope);

        return $this;
    }

    /**
     * Clears active scope for the current context.
     *
     * @return $this
     */
    public function clearScope()
    {
        session()->forget($this->getSessionKey(static::TYPE_PAGE));

        return $this;
    }

    /**
     * Returns whether an active parent is set for the current context.
     *
     * @return bool
     */
    public function hasListParent()
    {
        return session()->has($this->getSessionKey(static::TYPE_PARENT));
    }

    /**
     * Returns active parent for current context.
     *
     * @return null|false|array  associative: 'relation', 'key'; false for disabled filter; null for default/unset
     */
    public function getListParent()
    {
        $parent = session()->get($this->getSessionKey(static::TYPE_PARENT));

        if (null === $parent) {
            return null;
        }

        if (static::PARENT_DISABLE === $parent) {
            return false;
        }

        list($relation, $key) = explode('::', $parent);

        return compact('relation', 'key');
    }

    /**
     * Sets active parent for the current context.
     *
     * @param string|false $relation    false to disable default top-level only filter, otherwise model key string
     * @param mixed        $recordKey
     * @return $this
     */
    public function setListParent($relation, $recordKey = null)
    {
        if (false !== $relation && empty($relation)) {
            return $this->clearListParent();
        }

        if (false === $relation) {
            session()->set($this->getSessionKey(static::TYPE_PARENT), static::PARENT_DISABLE);
        } else {
            session()->set($this->getSessionKey(static::TYPE_PARENT), $relation . '::' . $recordKey);
        }

        return $this;
    }

    /**
     * Clears active parent for the current context.
     *
     * @return $this
     */
    public function clearListParent()
    {
        session()->forget($this->getSessionKey(static::TYPE_PARENT));

        return $this;
    }


    /**
     * Returns session key, optionally for a given type of data.
     *
     * @param string|null $type
     * @return string
     */
    protected function getSessionKey($type = null)
    {
        return $this->context
             . ($this->contextSub ? '[' . $this->contextSub . ']' : null)
             . ($type ? ':' . $type : null);
    }
}
