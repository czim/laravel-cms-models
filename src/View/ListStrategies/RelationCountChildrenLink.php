<?php
namespace Czim\CmsModels\View\ListStrategies;

use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Illuminate\Database\Eloquent\Model;

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
     * @return string
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

        return view(static::VIEW, [
            'count'        => $count,
            'link'         => $this->getChildrenLink($model->getKey(), $relationMethod, $modelClass),
            'childrenName' => $childrenName,
        ])->render();
    }

    /**
     * @param mixed  $parentKey         key of the current model
     * @param string $relationMethod
     * @param null   $modelClass        key of the related model (for which the current model is the parent)
     * @return bool|string
     */
    protected function getChildrenLink($parentKey, $relationMethod, $modelClass)
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

        $permissionPrefix = $routeHelper->getPermissionPrefixForModuleKey($module->getKey());

        if ( ! cms_auth()->can("{$permissionPrefix}show")) {
            return false;
        }

        $routePrefix = $routeHelper->getRouteNameForModelClass($modelClass, true);

        return cms_route("{$routePrefix}.index") . "?parent={$relationMethod}:{$parentKey}";
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

        return $info->verbose_name_plural;
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
