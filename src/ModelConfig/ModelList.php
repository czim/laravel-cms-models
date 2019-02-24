<?php
namespace Czim\CmsModels\ModelConfig;

class ModelList
{

    /**
     * @var array
     */
    protected $main = [];

    /**
     * @var ListFilter[]
     */
    protected $filters = [];


    public function toArray(): array
    {
        return $this->buildListArray();
    }


    /**
     * Disable filters even if they're specified
     *
     * @return ModelList|$this
     */
    public function disableFilters(): ModelList
    {
        $this->main['disable_filters'] = true;

        return $this;
    }

    /**
     * Sets a list of filter definitions
     *
     * In addition to any Field-defined filters.
     *
     * @param ListFilter[] $filters
     * @return ModelList
     */
    public function filters(array $filters): ModelList
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Set a sorting strategy to enable by default
     *
     * Stacks strategies if called more than once
     *
     * @param string $strategy  alias/name or <FQN>@<method> for custom ordering
     * @return ModelList|$this
     */
    public function defaultSortingStrategy(string $strategy): ModelList
    {
        if (is_string($this->main['default_sort'])) {
            $this->main['default_sort'] = [ $this->main['default_sort'] ];
            $this->main['default_sort'][] = $strategy;

            return $this;
        }

        $this->main['default_sort'] = $strategy;

        return $this;
    }

    /**
     * Set a list of sorting strategies to enable by default
     *
     * @param string[] $strategies  list of alias/name or <FQN>@<method> for custom ordering
     * @return ModelList|$this
     */
    public function defaultSortingStrategies(array $strategies): ModelList
    {
        array_map([$this, 'defaultSortingStrategy'], $strategies);

        return $this;
    }

    /**
     * Set the (default) page size
     *
     * @param int $size
     * @return ModelList|$this
     */
    public function pageSize(int $size): ModelList
    {
        $this->main['page_size'] = $size;

        return $this;
    }

    /**
     * Set the available page size choices that users can switch to manually
     *
     * @param int[] $sizes      first value will be default
     * @return ModelList|$this
     */
    public function pageSizes(array $sizes): ModelList
    {
        $this->main['page_size'] = $sizes;

        return $this;
    }

    /**
     * Disable the use and display of scopes, even if specified
     *
     * @return ModelList|$this
     */
    public function disableScopes(): ModelList
    {
        $this->main['disable_scopes'] = true;

        return $this;
    }

    /**
     * Sets scopes for the listing
     *
     * @param string[] $scopes  scopes or scoping strategies, keyed by scope name
     * @return ModelList|$this
     */
    public function scopes(array $scopes): ModelList
    {
        $this->main['scopes'] = $scopes;

        return $this;
    }

    /**
     * Adds an action to be performed when clicking a row.
     *
     * Will stack if called multiple times, in order.
     * The first action that is permitted, is performed.
     *
     * @param string $action    action alias
     * @return ModelList|$this
     */
    public function rowClickAction(string $action): ModelList
    {
        if ( ! array_has($this->main, 'default_action')) {
            $this->main['default_action'] = [];
        }

        $this->main['default_action'][] = $action;

        return $this;
    }

    /**
     * Set a view to include before the listing
     *
     * @param string $view
     * @param array  $variables     optional list of variable names to pass through to the included view
     * @return ModelList
     */
    public function viewBefore(string $view, array $variables = []): ModelList
    {
        $this->main['before'] = compact('view', 'variables');

        return $this;
    }

    /**
     * Set a view to include after the listing
     *
     * @param string $view
     * @param array  $variables     optional list of variable names to pass through to the included view
     * @return ModelList
     */
    public function viewAfter(string $view, array $variables = []): ModelList
    {
        $this->main['after'] = compact('view', 'variables');

        return $this;
    }

    // ------------------------------------------------------------------------------
    //      List parent relations
    // ------------------------------------------------------------------------------

    /**
     * Hide everything but top-level list parents by default to this relation
     *
     * Useful to remove clutter for nested content with a click-through-to-children setup.
     * Set to relation method name that must be present in 'parents'.
     *
     * @param string $relation
     * @return ModelList|$this
     */
    public function parentRelationActiveByDefault(string $relation): ModelList
    {
        $this->main['default_top_relation'] = $relation;

        if ( ! array_get($this->main, 'parents')) {
            // Assume the relation (= the field) if no parents set yet.

            $this->main['parents'] = [
                [
                    'relation' => $relation,
                    'field'    => $relation,
                ],
            ];
        }

        return $this;
    }

    /**
     * List parents for list hierarchy handling
     *
     * The relations by which this listing may be 'filtered'.
     *
     * @param string[] $relations  either relation names, or key-value pairs: relation => field
     * @return ModelList|$this
     */
    public function parentRelations(array $relations): ModelList
    {
        $this->main['parents'] = [];

        foreach ($relations as $key => $value) {

            if (is_string($key)) {
                $parent = [
                    'relation' => $key,
                    'field'    => $value,
                ];
            } else {
                $parent = [
                    'relation' => $value,
                    'field'    => $value,
                ];
            }

            $this->main['parents'][] = $parent;
        }

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Orderable
    // ------------------------------------------------------------------------------

    /**
     * Allow manual ordering by dragging and dropping records
     *
     * This may also be enabled by calling orderable() on a corresponding Field entry.
     *
     * @param bool $orderable
     * @return ModelList|$this
     */
    public function orderable(bool $orderable = true): ModelList
    {
        $this->main['orderable'] = $orderable;

        return $this;
    }

    /**
     * The strategy by which the model can be ordered
     *
     * For now, this should always be 'listify'.
     *
     * @param string $strategy
     * @return ModelList|$this
     */
    public function orderableStrategy(string $strategy): ModelList
    {
        $this->main['orderable_strategy'] = $strategy ?: 'listify';

        return $this;
    }

    /**
     * The column used for the order strategy ('position' for listify)
     *
     * This may also be set by calling orderable() on a corresponding Field entry.
     * This wil also automatically enable orderable.
     *
     * @param string $column
     * @return ModelList|$this
     */
    public function orderableColumn(string $column): ModelList
    {
        $this->main['orderable_column'] = $column;

        return $this;
    }

    /**
     * If listify is scoped in a way to restrict it for a relation's foreign key,
     * set the relation name method
     *
     * @param string $relation
     * @return ModelList|$this
     */
    public function orderableRelationScope(string $relation): ModelList
    {
        $this->main['order_scope_relation'] = $relation;

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Activatable
    // ------------------------------------------------------------------------------

    /**
     * Allow manual activation
     *
     * Whether the model may be activated/deactived through the listing;
     * ie. whether it has a manipulable 'active' flag.
     *
     * This may also be enabled by calling activatable() on a corresponding Field entry.
     *
     * @param bool $activatable
     * @return ModelList|$this
     */
    public function activatable(bool $activatable = true): ModelList
    {
        $this->main['activatable'] = $activatable;

        return $this;
    }

    /**
     * The column used for the activate strategy ('active' by default), will be toggled true/false
     *
     *
     * This may also be set by calling activatable() on a corresponding Field entry.
     * This wil also automatically enable activatable.
     *
     * @param string $column
     * @return ModelList|$this
     */
    public function activatableColumn(string $column): ModelList
    {
        $this->main['activatable_column'] = $column;

        return $this;
    }


    protected function buildListArray(): array
    {
        $array = $this->main;

        $this->applyFiltersDataToArray($array);

        return $array;
    }

    protected function applyFiltersDataToArray(array &$array): void
    {
        if ( ! count($this->filters)) {
            return;
        }

        foreach ($this->filters as $filter) {

            $array['filters'][ $filter->getKey() ] = $filter->toArray();
        }
    }

}
