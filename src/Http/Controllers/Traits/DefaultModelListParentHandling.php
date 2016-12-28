<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
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
     * @var string
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

        if ($update) {
            $this->updateActiveParent();
        }

        if ($this->getListMemory()->hasListParent()) {
            $this->retrieveActiveParentFromSession();
        }

        $this->collectListParentHierarchy();

        return $this;
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
     * Updates the active list parent if the request parameter is set.
     *
     * @return $this
     */
    protected function updateActiveParent()
    {
        if (request()->exists('parent')) {

            $parent = request()->get('parent');

            if (is_string($parent) && false === strpos($parent, ':')) {
                $parent = null;
            }

            if ( ! $parent) {
                $this->listParentRelation  = $parent;
                $this->listParentRecordKey = null;
            } else {
                list($this->listParentRelation, $this->listParentRecordKey) = explode(':', $parent, 2);
            }

            $this->storeActiveParentInSession();
        }

        return $this;
    }

    /**
     * Stores the currently set filters in the session.
     */
    protected function storeActiveParentInSession()
    {
        $this->getListMemory()->setListParent($this->listParentRelation, $this->listParentRecordKey);
    }

    /**
     * Retrieves the filters from the session and restores them.
     */
    protected function retrieveActiveParentFromSession()
    {
        $parent = $this->getListMemory()->getListParent();

        if (false === $parent || null === $parent) {
            $this->listParentRelation  = $parent;
            $this->listParentRecordKey = null;
            return;
        }

        $this->listParentRelation  = array_get($parent, 'relation');
        $this->listParentRecordKey = array_get($parent, 'key');
    }

    /**
     * Looks up the current full list parent hierarchy chain to the top level.
     */
    protected function collectListParentHierarchy()
    {
        $this->listParents = [];

        if ( ! $this->listParentRelation || ! $this->listParentRelation || null === $this->listParentRecordKey) {
            return;
        }

        $model = $this->getNewModelInstance();

        $info = $this->getModelInformationForModel($model);

        $this->listParents = $this->getListParentInformation(
            $model,
            $this->listParentRelation,
            $this->listParentRecordKey,
            $info
        );
    }

    /**
     * Returns information about a list parent in the chain.
     *
     * Exceptions will be thrown if there are logical problems with the chain.
     *
     * @param Model                                      $model
     * @param string                                     $relation
     * @param mixed                                      $key
     * @param ModelInformationInterface|ModelInformation $info
     * @return ListParentData[]
     */
    protected function getListParentInformation(Model $model, $relation, $key, ModelInformationInterface $info)
    {
        /** @var Relation $relation */
        $relation = $model->{$relation}();

        $parentModel = $this->getModelByKey($relation->getRelated(), $key);

        if ( ! $parentModel) {
            return [];
        }

        $parentInfo = $this->getModelInformationForModel($parentModel);

        $list = [];

        // Check for another link in the chain and recurse if there is
        if ( ! empty($info->list->parents)) {

            // Look up session data for list parent
            $parentListParent = $this->getListParentForModelFromSession($parentModel);

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
            'model'             => $parentModel,
            'information'       => $parentInfo,
            'module_key'        => $module->getKey(),
            'route_prefix'      => $routeHelper->getRouteNameForModelClass(get_class($parentModel), true),
            'permission_prefix' => $routeHelper->getPermissionPrefixForModuleKey($module->getKey()),
        ]);

        return $list;
    }

    /**
     * Returns nested list parent information for a given model.
     *
     * @param Model                $model
     * @param ModuleInterface|null $module
     * @return array|false|null assoc with 'relation', 'key'
     */
    protected function getListParentForModelFromSession(Model $model, ModuleInterface $module = null)
    {
        if (null === $module) {
            $module = $this->getModuleByModel($model);
        }

        if ( ! $module) {
            return [];
        }

        $key = $module->getKey();

        /** @var ModelListMemoryInterface $memory */
        $memory = app(ModelListMemoryInterface::class);

        $oldContext = $memory->getContext();
        $oldSubContext = $memory->getSubContext();

        $memory->setContext($key);
        $memory->setSubContext(null);

        $listParent = $memory->getListParent();

        $memory->setContext($oldContext);
        $memory->setSubContext($oldSubContext);

        return $listParent;
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
     * @return Model
     */
    abstract protected function getNewModelInstance();

    /**
     * @return ModelListMemoryInterface
     */
    abstract protected function getListMemory();

}
