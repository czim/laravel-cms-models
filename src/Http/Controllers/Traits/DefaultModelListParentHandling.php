<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;
use Czim\CmsModels\Support\Data\ListParentData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

trait DefaultModelListParentHandling
{

    /**
     * The current parent hierarchy 'breadcrumbs'.
     *
     * @var ListParentData[]
     */
    protected $listParents = [];

    /**
     * The current active list parent relation.
     *
     * String relation method name, or NULL to fall back to default (show everything or only top),
     * or FALSE to apply the opposite of the default.
     *
     * @var string|null|false
     */
    protected $listParentRelation;

    /**
     * The current active list parent record key.
     *
     * @var string
     */
    protected $listParentRecordKey;


    /**
     * @return array
     */
    protected function getListParents()
    {
        return $this->listParents;
    }

    /**
     * Checks and loads list parent from the session.
     *
     * @param bool $update  whether to check for updates through the 'parent' request param
     * @return $this
     */
    protected function checkListParents($update = true)
    {
        if (empty($this->getModelInformation()->list->parents)) {
            return $this;
        }

        $this->retrieveActiveParentFromSession();
        $this->collectListParentHierarchy();

        if ($update) {
            $this->updateActiveParent();
        }

        $this->enrichListParents();

        return $this;
    }

    /**
     * Returns whether a list parent is currently active.
     *
     * @return bool
     */
    protected function hasActiveListParent()
    {
        return (bool) $this->listParentRelation;
    }

    /**
     * Applies the context for the current list parent scope.
     *
     * Must be called before other list memory is accessed, since it affects the sub-context of the memory.
     *
     * @return $this
     */
    protected function applyListParentContext()
    {
        $parents = $this->getModelInformation()->list->parents;

        if (empty($parents)) {
            return $this;
        }

        if ($this->listParentRelation) {
            $contextKey = $this->listParentRelation . ':' . $this->listParentRecordKey;
        } else {
            $contextKey = null;
        }

        $this->getListMemory()->setSubContext($contextKey);

        return $this;
    }

    /**
     * Applies the active list parent as a filter on a query builder.
     *
     * @param Builder $query
     * @return $this
     */
    protected function applyListParentToQuery($query)
    {
        if ( ! $this->listParentRelation) {

            $relation = $this->getModelInformation()->list->default_top_relation;

            // If the default is to restrict to top-level only,
            // or the reverse with a user-initiated reversal (relation = false):
            // Restrict the query to top level.
            if ($relation || false === $this->listParentRelation) {
                $query->has($relation, '<', 1);
            }

            return $this;
        }

        // Use list parent information collected to get the model
        /** @var ListParentData $parentInfo */
        $parentInfo = array_last($this->listParents);

        if ( ! $parentInfo) {
            return $this;
        }

        $query->whereHas($this->listParentRelation, function ($query) use ($parentInfo) {
            /** @var Builder $query */
            $query->where($parentInfo->model->getKeyName(), $this->listParentRecordKey);
        });

        return $this;
    }

    /**
     * Looks up the current full list parent hierarchy chain to the top level.
     *
     * @param string|null $relation     if not set, uses currently stored values
     * @param mixed|null  $key
     */
    protected function collectListParentHierarchy($relation = null, $key = null)
    {
        $this->listParents = [];

        if (null === $relation) {
            $relation = $this->listParentRelation;
            $key      = $this->listParentRecordKey;
        }

        if ( ! $relation || null === $key) {
            return;
        }

        $model = $this->getNewModelInstance();

        $info = $this->getModelInformationForModel($model);

        $this->listParents = $this->getListParentInformation(
            $model,
            $this->listParentRelation,
            $this->listParentRecordKey,
            $info,
            true
        );
    }

    /**
     * Updates the active list parent if the request parameter is set.
     *
     * @return $this
     */
    protected function updateActiveParent()
    {
        // Check for either a full hierarchy or a 'current level' update
        if (request()->exists('parents')) {

            $parents = request()->get('parents');

            if ( ! empty($parents)) {

                if (is_string($parents)) {
                    $parents = explode(';', $parents);
                }

                $parents = array_values($parents);

                // No need to update if everything is the same down the chain
                if (count($this->listParents) == count($parents)) {
                    $same = true;
                    foreach ($this->listParents as $index => $currentParent) {
                        if ($currentParent->relation . ':'. $currentParent->key !== $parents[$index]) {
                            $same = false;
                            break;
                        }
                    }

                    if ($same) {
                        return $this;
                    }
                }

                $this->clearEntireListParentHierarchy();

                // Update, working backwards, starting at the current list; then re-collect the active parent chain
                $this->updateActiveParents(array_reverse($parents));

            } else {

                // No need to update if no current memorized parent chain
                if ( ! count($this->listParents)) {
                    return $this;
                }

                $this->clearEntireListParentHierarchy();
            }

        } elseif (request()->exists('parent')) {

            $parent = request()->get('parent');

            // Interpret special parent values
            $parent = $this->normalizeListParentParameter($parent);

            // If nothing has changed, no need to update
            if (    ! $parent && $this->listParentRelation == $parent
                ||  $parent == $this->listParentRelation . ':' . $this->listParentRecordKey
            ) {
                return $this;
            }

            if ( ! $parent) {
                // Reset this level to its previous list parent, if any
                $this->listParentRelation  = $parent;
                $this->listParentRecordKey = null;
            } else {

                // If the parent is already present in the current history, switch to it
                if ( ! count(array_filter(
                    $this->listParents,
                    function ($checkParent) use ($parent) {
                        /** @var ListParentData $checkParent */
                        return $checkParent->relation . ':' . $checkParent->key == $parent;
                    }
                ))) {
                    // Not set yet, prepare old parent as one layer deeper

                    $this->setListParentDataInMemory(
                        $this->listParentRelation,
                        $this->listParentRecordKey,
                        $parent
                    );
                }

                list($this->listParentRelation, $this->listParentRecordKey) = explode(':', $parent, 2);
            }
        }

        $this->storeActiveParentInSession();
        $this->collectListParentHierarchy();

        return $this;
    }

    /**
     * Normalizes 'parent' query parameter for list parent update.
     *
     * @param mixed $parent
     * @return int|null
     */
    protected function normalizeListParentParameter($parent)
    {
        if (is_integer($parent) && $parent < 0 || is_string($parent) && preg_match('#-\d+#', $parent)) {
            $parent = (int) $parent;

            if ($parent < 0) {
                // The the parent that conforms to the index in the parent chain
                // This only works within the context of this model, so no cross-model logic is expected
                $index = count($this->listParents) + $parent - 1;

                if ($index >= 0 && isset($this->listParents[ $index ])) {
                    $parent = $this->listParents[ $index ]->relation . ':' . $this->listParents[ $index ]->key;
                } else {
                    $parent = null;
                }

            } else {
                $parent = null;
            }
        }

        if (is_string($parent) && false === strpos($parent, ':')) {
            $parent = null;
        }

        return $parent;
    }

    /**
     * Update a full list parent chain.
     *
     * @param array $parents    should be last-first ordered, strings with :-separated relaton:key
     */
    protected function updateActiveParents(array $parents)
    {
        $modelInfo       = $this->getModelInformation();
        $modelClass      = $modelInfo->modelClass();
        $model           = new $modelClass;
        $previousContext = null;
        $previousParent  = array_shift($parents);


        // Store top level parent as active
        if ( ! $previousParent || is_string($previousParent) && false === strpos($previousParent, ':')) {
            $this->listParentRelation  = $previousParent === false ? false : null;
            $this->listParentRecordKey = null;
        } else {
            list($this->listParentRelation, $this->listParentRecordKey) = explode(':', $previousParent, 2);
        }

        $count = 0;

        // Add 'end' marker to force overwriting a previously longer chain link as a terminator
        $parents[] = null;

        // Loop through further parents, where $parent is what $previousParent refers back to
        foreach ($parents as $parent) {

            $count++;

            // If we have no interpretable parent to use as sub-context, stop
            if ( ! $previousParent || is_string($previousParent) && false === strpos($previousParent, ':')) {
                break;
            }

            // If we have no interpretable parent as target, clear the context, and stop
            if ( ! $parent || is_string($parent) && false === strpos($parent, ':')) {
                $this->setListParentDataInMemory(null, null, $previousParent, $previousContext);
                break;
            }

            list($relation, $key) = explode(':', $parent, 2);

            // Set new parent at this level
            $this->setListParentDataInMemory($relation, $key, $previousParent, $previousContext);

            // If this is the last parent, then there is no need to prepare for the next iteration
            if ($count == count($parents)) {
                break;
            }

            // If the context (model) changed, determine the new context and remember it for the next iteration
            $listParentInfo = $this->getListParentInformation($model, $relation, $key, $modelInfo);

            if ( ! count($listParentInfo)) {
                break;
            }

            // Check and update the model class (for the memory context) if necessary.
            // This is only relevant for list parent reference across models.
            $listParentInfo = head($listParentInfo);
            /** @var ListParentData $listParentInfo */


            if ($modelClass !== get_class($listParentInfo->model)) {
                $model           = $listParentInfo->model;
                $modelClass      = get_class($model);
                $previousContext = $this->getModelSessionKey($this->getModuleHelper()->modelSlug($model));
            }

            $previousParent = $parent;
        }
    }

    /**
     * Enriches list parent data for view.
     */
    protected function enrichListParents()
    {
        if (empty($this->listParents)) {
            return;
        }

        // Mark for each list parents entry how the query params should be set up
        // 1. for same-model, top level: parent=0 (or -# to match)
        // 2. for different model, first level: no param
        // 3. for same-model, levels: parent=-# reset this many levels
        // 4. for absolute top level: parents=

        $queries       = [];
        $backCounter   = 0;
        $previousModel = $this->getModelInformation()->modelClass();

        /** @var ListParentData[] $reversed */
        $reversed = array_reverse($this->listParents);

        foreach ($reversed as $parent) {

            if ($previousModel != get_class($parent->model)) {
                $queries[] = null;
            } else {
                $backCounter++;
                $queries[] = 'parent=-' . $backCounter;
            }

            $previousModel = get_class($parent->model);
        }

        $queries = array_values(array_reverse($queries));

        foreach ($queries as $index => $query) {
            $this->listParents[$index]->query = $query;
        }
    }


    // ------------------------------------------------------------------------------
    //      Access & Manipulate List Memory
    // ------------------------------------------------------------------------------

    /**
     * Clears list parent chain as currently memorized.
     */
    protected function clearEntireListParentHierarchy()
    {
        foreach ($this->listParents as $parent) {

            if ( ! $parent || is_string($parent) && false === strpos($parent, ':')) {
                break;
            }

            // Unset parent at this level
            $context = $this->getModelSessionKey(
                $this->getModuleHelper()->modelSlug(get_class($parent->model))
            );

            $this->setListParentDataInMemory(null, null, $parent->relation . ':' . $parent->key, $context);
        }

        $this->setListParentDataInMemory(null, null, $this->globalSubContext());

        $this->listParentRelation  = null;
        $this->listParentRecordKey = null;
        $this->listParents         = [];
    }

    /**
     * Stores the currently set filters in the session.
     */
    protected function storeActiveParentInSession()
    {
        $this->setListParentDataInMemory(
            $this->listParentRelation,
            $this->listParentRecordKey,
            $this->globalSubContext()
        );
    }

    /**
     * Retrieves the currently set 'global' list parent from the session and stores it locally.
     */
    protected function retrieveActiveParentFromSession()
    {
        $parent = $this->getListParentDataFromMemory($this->globalSubContext());

        if (false === $parent || null === $parent) {
            $this->listParentRelation  = $parent;
            $this->listParentRecordKey = null;
            return;
        }

        $this->listParentRelation  = array_get($parent, 'relation');
        $this->listParentRecordKey = array_get($parent, 'key');
    }

    /**
     * Returns information about a list parent, or all list parents in the chain.
     *
     * Exceptions will be thrown if there are logical problems with the chain.
     *
     * @param Model                                      $model
     * @param string                                     $relation
     * @param mixed                                      $key
     * @param ModelInformationInterface|ModelInformation $info
     * @param bool                                       $recurse   if true, looks up entire chain
     * @return ListParentData[]
     */
    protected function getListParentInformation(
        Model $model,
        $relation,
        $key,
        ModelInformationInterface $info,
        $recurse = false
    ) {
        // Don't attempt to load relation methods if the parent relation is not configured
        if (    empty($info->list->parents)
            ||  ! count(
                    array_filter(
                        $info->list->parents,
                        function ($parent) use ($relation) { return $parent->relation == $relation; }
                    )
                )
        ) {
            return [];
        }

        /** @var Relation $relationInstance */
        $relationInstance = $model->{$relation}();

        $parentModel = $this->getModelByKey($relationInstance->getRelated(), $key);

        if ( ! $parentModel) {
            return [];
        }

        $parentInfo = $this->getModelInformationForModel($parentModel);

        $list = [];

        // Check for another link in the chain and recurse if there is
        if ($recurse && ! empty($parentInfo->list->parents)) {

            // When switching from one model to another, use the global subcontext,
            // otherwise, use the subcontext that contains the next step
            // Look up session data for list parent

            $parentListParent = $this->getListParentDataFromMemory(
                get_class($model) != get_class($parentModel) ? $this->globalSubContext() : $relation . ':' . $key,
                $this->getModelSessionKey($this->getModuleHelper()->modelSlug($parentModel))
            );

            if (is_array($parentListParent)) {
                $list = $this->getListParentInformation(
                    $parentModel,
                    array_get($parentListParent, 'relation'),
                    array_get($parentListParent, 'key'),
                    $parentInfo
                );
            }
        }

        $module = $this->getModuleByModel($parentModel);

        if ( ! $module) {
            return [];
        }

        /** @var RouteHelperInterface $routeHelper */
        $routeHelper = app(RouteHelperInterface::class);

        $list[] = new ListParentData([
            'relation'          => $relation,
            'key'               => $key,
            'model'             => $parentModel,
            'information'       => $parentInfo,
            'module_key'        => $module->getKey(),
            'route_prefix'      => $routeHelper->getRouteNameForModelClass(get_class($parentModel), true),
            'permission_prefix' => $routeHelper->getPermissionPrefixForModelSlug(
                $this->getModuleHelper()->modelInformationKeyForModel($parentModel)
            ),
        ]);

        return $list;
    }

    /**
     * Returns model instance by key.
     *
     * @param Model $model
     * @param mixed $key
     * @return Model|null
     */
    protected function getModelByKey(Model $model, $key)
    {
        return $model::withoutGlobalScopes()
            ->where($model->getKeyName(), $key)
            ->first();
    }

    /**
     * Returns the module for a given model.
     *
     * @param Model $model
     * @return ModuleInterface|false
     */
    protected function getModuleByModel(Model $model)
    {
        /** @var ModuleManagerInterface $modules */
        $modules = app(ModuleManagerInterface::class);

        return $modules->getByAssociatedClass(get_class($model));
    }

    /**
     * @param Model $model
     * @return ModelInformation|false
     */
    protected function getModelInformationForModel(Model $model)
    {
        /** @var ModelInformationRepositoryInterface $infoRepository */
        $infoRepository = app(ModelInformationRepositoryInterface::class);

        if ( ! ($info = $infoRepository->getByModel($model))) {
            throw new UnexpectedValueException(
                "List parent hierarchy relation model "
                . get_class($model) . " is not part of the CMS."
            );
        }

        return $info;
    }

    /**
     * Retrieves list parent data from memory and resets memory context.
     *
     * @param string|null $subContext
     * @param string|null $context
     * @return array|false|null
     */
    protected function getListParentDataFromMemory($subContext, $context = null)
    {
        $memory = $this->getListMemory();

        $oldContext    = $memory->getContext();
        $oldSubContext = $memory->getSubContext();

        if (null !== $context) {
            $memory->setContext($context);
        }

        $memory->setSubContext($subContext);

        $listParent = $memory->getListParent();

        if (null !== $context) {
            $memory->setContext($oldContext);
        }

        $memory->setSubContext($oldSubContext);

        return $listParent;
    }

    /**
     * Stores list parent data in memory and resets memory context.
     *
     * @param string|null|false $relation
     * @param mixed|null        $key
     * @param string|null       $subContext
     * @param string|null       $context
     */
    protected function setListParentDataInMemory($relation, $key, $subContext, $context = null)
    {
        $memory = $this->getListMemory();

        $oldContext    = $memory->getContext();
        $oldSubContext = $memory->getSubContext();

        if (null !== $context) {
            $memory->setContext($context);
        }

        $memory->setSubContext($subContext);

        $memory->setListParent($relation, $key);

        if (null !== $context) {
            $memory->setContext($oldContext);
        }

        $memory->setSubContext($oldSubContext);
    }

    /**
     * Returns sub-context string for storing global list parent data in.
     *
     * This sub-context should hold the 'active' list parent.
     *
     * @return string
     */
    protected function globalSubContext()
    {
        return '__global__';
    }



    // ------------------------------------------------------------------------------
    //      Dependencies
    // ------------------------------------------------------------------------------

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
     * @return ModuleHelperInterface
     */
    abstract protected function getModuleHelper();

    /**
     * @return Model
     */
    abstract protected function getNewModelInstance();

    /**
     * @return ModelListMemoryInterface
     */
    abstract protected function getListMemory();

    /**
     * @param string|null $modelSlug    defaults to current module key
     * @return string
     */
    abstract protected function getModelSessionKey($modelSlug = null);

}
