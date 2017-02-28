<?php

namespace Czim\CmsModels\View\ListStrategies;

use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Illuminate\Database\Eloquent\Model;

class RelationReferenceLink extends RelationReference
{
    /**
     * @var RouteHelperInterface
     */
    protected $routeHelper;

    /**
     * RelationEditLink constructor.
     *
     * @param RouteHelperInterface|null $routeHelper
     */
    public function __construct(RouteHelperInterface $routeHelper = null)
    {
        if (null === $routeHelper) {
            $routeHelper = app(RouteHelperInterface::class);
        }

        $this->routeHelper = $routeHelper;
    }

    /**
     * Returns a reference representation for a single model.
     *
     * @param Model $model
     * @return string
     */
    protected function getReference(Model $model)
    {
        $reference = $this->getReferenceValue($model);

        return $this->wrapWithLink($reference, $this->getLinkForReferenceModel($model));
    }

    /**
     * Wraps given reference string with hyperlink to given url
     *
     * @param string $reference
     * @param string $url
     * @return string
     */
    protected function wrapWithLink($reference, $url)
    {
        return '<a href="' . $url . '">' . $this->wrapReference($reference) . '</a>';
    }

    /**
     * Generates edit link for given model
     *
     * @param Model $model
     * @return string
     */
    protected function getLinkForReferenceModel(Model $model)
    {
        $routeName = $this->routeHelper->getRouteNameForModelClass(
            get_class($model),
            true
        );

        $action = $this->determineRouteAction($model);

        return route($routeName . '.' . $action, $model->getKey());
    }

    /**
     * Determines route action for given model. We will always try for the edit action, but if
     * the permission check fails we will fall back to the show action.
     *
     * @param Model $model
     * @return string
     */
    protected function determineRouteAction(Model $model)
    {
        // Check the permissions to determine whether we're creating an
        // edit link or a show link
        $action = 'edit';
        $permissionPrefix = $this->routeHelper->getPermissionPrefixForModelSlug(
            $this->routeHelper->getRouteSlugForModelClass(get_class($model))
        );

        // Change the action to show if we don't have edit permissions
        if ( ! cms_auth()->can($permissionPrefix . $action)) {
            $action = 'show';
        }

        return $action;
    }

}
