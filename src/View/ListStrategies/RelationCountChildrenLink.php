<?php
namespace Czim\CmsModels\View\ListStrategies;

use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class RelationCountChildrenLink
 *
 * Strategy for showing a 'to children' link, for records that have at least one child.
 * Uses the list parents logic: links to child model index with the 'parent' request parameter.
 */
class RelationCountChildrenLink extends RelationCount
{
    const VIEW = 'cms-models::model.partials.list.strategies.relation_count_children_link';

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string|View
     */
    public function render(Model $model, $source)
    {
        $relation = $this->getActualNestedRelation($model, $source);

        $count = $this->getCount($relation);

        if ( ! $count) {
            return '<span class="relation-count count-empty">&nbsp;</span>';
        }

        // Use the options or relation to get the
        $relationMethod = array_get($this->listColumnData->options(), 'relation');
        $modelClass     = array_get($this->listColumnData->options(), 'model', get_class($relation->getRelated()));

        $childrenName = $this->getVerboseChildrenName($modelClass);

        if ($this->isMorphToRelation($modelClass, $relationMethod)) {
            $parentIndicator = $this->getMorphTypeForModel($model) . ':' . $model->getKey();
        } else {
            $parentIndicator = $model->getKey();
        }

        return view(static::VIEW, [
            'count'        => $count,
            'link'         => $this->getChildrenLink($parentIndicator, $relationMethod, $modelClass),
            'childrenName' => $childrenName,
        ]);
    }

    /**
     * Returns whether the relation on a model class is a MorphTo relation.
     *
     * @param string $modelClass
     * @param string $relation
     * @return bool
     */
    protected function isMorphToRelation($modelClass, $relation)
    {
        $relationInstance = (new $modelClass)->{$relation}();

        return $relationInstance instanceof MorphTo;
    }

    /**
     * @param Model $model
     * @return string
     */
    protected function getMorphTypeForModel(Model $model)
    {
        $map = Relation::morphMap();

        $class = ltrim(get_class($model), '\\');

        if (empty($map)) {
            return $class;
        }

        if (false !== ($type = array_search($class, $map))) {
            return $type;
        }

        return $class;
    }

    /**
     * @param mixed  $parentIndicator   key of the current model, or class/type:key for morphTo
     * @param string $relationMethod
     * @param null   $modelClass        key of the related model (for which the current model is the parent)
     * @return bool|string
     */
    protected function getChildrenLink($parentIndicator, $relationMethod, $modelClass)
    {
        if (empty($relationMethod)) {
            throw new \UnexpectedValueException(get_class($this) . ' requires option.relation to be set!');
        }

        /** @var ModuleManagerInterface $modules */
        $modules = app(ModuleManagerInterface::class);

        $module = $modules->getByAssociatedClass($modelClass);

        if ( ! $module) {
            return false;
        }

        /** @var RouteHelperInterface $routeHelper */
        $routeHelper = app(RouteHelperInterface::class);
        /** @var ModuleHelperInterface $moduleHelper */
        $moduleHelper = app(ModuleHelperInterface::class);

        $permissionPrefix = $routeHelper->getPermissionPrefixForModelSlug($moduleHelper->modelSlug($modelClass));

        if ( ! cms_auth()->can("{$permissionPrefix}show")) {
            return false;
        }

        $routePrefix = $routeHelper->getRouteNameForModelClass($modelClass, true);

        return cms_route("{$routePrefix}.index") . "?parent={$relationMethod}:{$parentIndicator}";
    }

    /**
     * Returns name, plural for child records.
     *
     * @param $modelClass
     * @return string
     */
    protected function getVerboseChildrenName($modelClass)
    {
        $info = $this->getInformationRepository()->getByModelClass($modelClass);

        if ( ! $info) {
            return cms_trans('models.list-parents.models');
        }

        return $info->labelPlural();
    }

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string|null
     */
    public function style(Model $model, $source)
    {
        return null;
    }

}
