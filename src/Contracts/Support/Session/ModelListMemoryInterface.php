<?php
namespace Czim\CmsModels\Contracts\Support\Session;

interface ModelListMemoryInterface
{

    /**
     * @return null|string
     */
    public function getContext();

    /**
     * @param string $key
     * @return $this
     */
    public function setContext($key);

    /**
     * @return string|null
     */
    public function getSubContext();

    /**
     * @param string $key
     * @return $this
     */
    public function setSubContext($key);


    /**
     * Returns whether filters are set for the current context.
     *
     * @return bool
     */
    public function hasFilters();

    /**
     * Returns filters set for current context.
     *
     * @return array
     */
    public function getFilters();

    /**
     * Sets filter data for the current context.
     *
     * @param array $filters
     * @return $this
     */
    public function setFilters(array $filters);

    /**
     * Clears all current filters for the current context.
     *
     * @return $this
     */
    public function clearFilters();

    /**
     * Returns whether sortData are set for the current context.
     *
     * @return bool
     */
    public function hasSortData();

    /**
     * Returns sortData set for current context.
     *
     * @return array    assoc, 'column', 'direction'
     */
    public function getSortData();

    /**
     * Sets filter data for the current context.
     *
     * @param string      $column
     * @param null|string $direction
     * @return $this
     */
    public function setSortData($column, $direction = null);

    /**
     * Clears all current sortData for the current context.
     *
     * @return $this
     */
    public function clearSortData();

    /**
     * Returns whether an active page is set for the current context.
     *
     * @return bool
     */
    public function hasPage();

    /**
     * Returns active page for current context.
     *
     * @return array
     */
    public function getPage();

    /**
     * Sets active page for the current context.
     *
     * @param int $page
     * @return $this
     */
    public function setPage($page);

    /**
     * Clears active page for the current context.
     *
     * @return $this
     */
    public function clearPage();

    /**
     * Returns whether an active page size is set for the current context.
     *
     * @return bool
     */
    public function hasPageSize();

    /**
     * Returns active page size for current context.
     *
     * @return array
     */
    public function getPageSize();

    /**
     * Sets active page size for the current context.
     *
     * @param int $size
     * @return $this
     */
    public function setPageSize($size);

    /**
     * Clears active page size for the current context.
     *
     * @return $this
     */
    public function clearPageSize();

    /**
     * Returns whether an active scope is set for the current context.
     *
     * @return bool
     */
    public function hasScope();

    /**
     * Returns active scope for current context.
     *
     * @return array
     */
    public function getScope();

    /**
     * Sets active scope for the current context.
     *
     * @param string $scope
     * @return $this
     */
    public function setScope($scope);

    /**
     * Clears active scope for the current context.
     *
     * @return $this
     */
    public function clearScope();

}
