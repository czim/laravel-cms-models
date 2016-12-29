<?php
namespace Czim\CmsModels\View\ActionStrategies;

use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Illuminate\Database\Eloquent\Model;

class EditStrategy extends AbstractActionStrategy
{
    const ROUTE_POSTFIX = '.edit';

    /**
     * Whether the action is considered permissible.
     *
     * @var bool
     */
    protected $isPermitted = false;

    /**
     * The prefix to use for making routes.
     *
     * @var string
     */
    protected $routePrefix;



    /**
     * Performs initialization.
     * Override this to customize strategy implementations.
     */
    protected function performInit()
    {
        /** @var RouteHelperInterface $routeHelper */
        $routeHelper = app(RouteHelperInterface::class);

        $this->routePrefix = $routeHelper->getRouteNameForModelClass($this->modelClass, true);

        $permissions = $this->actionData->permissions();

        if (empty($permissions)) {
            $this->isPermitted = true;
        } else {
            $this->isPermitted = cms_auth()->can($permissions);
        }
    }

    /**
     * Returns the action link for a given model instance.
     *
     * @param Model $model
     * @return string|false
     */
    public function link(Model $model)
    {
        if ( ! $this->routePrefix || ! $this->isPermitted) {
            return false;
        }

        return route($this->routePrefix . static::ROUTE_POSTFIX, [ $model->getKey() ]);
    }

}
