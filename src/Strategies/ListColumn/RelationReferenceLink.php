<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Illuminate\Database\Eloquent\Model;

class RelationReferenceLink extends RelationReference
{

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
     * Wraps given reference string with hyperlink to given url.
     *
     * @param string $reference
     * @param string $url
     * @return string
     */
    protected function wrapWithLink($reference, $url)
    {
        return '<a href="' . e($url) . '">' . $this->wrapReference($reference) . '</a>';
    }

    /**
     * Returns action link for given model.
     *
     * @param Model $model
     * @return string
     */
    protected function getLinkForReferenceModel(Model $model)
    {
        $routeName = $this->getRouteHelper()->getRouteNameForModelClass(get_class($model), true);

        $action = $this->determineRouteAction($model);

        return route($routeName . '.' . $action, $model->getKey());
    }

    /**
     * Returns route action for given model.
     *
     * The edit action is default, with a fallback to show if no permission is granted.
     *
     * @param Model $model
     * @return string
     */
    protected function determineRouteAction(Model $model)
    {
        $routeHelper = $this->getRouteHelper();

        $permissionPrefix = $routeHelper->getPermissionPrefixForModelSlug(
            $routeHelper->getRouteSlugForModelClass(get_class($model))
        );

        // Change the action to show if we don't have edit permissions
        if ( ! cms_auth()->can($permissionPrefix . 'edit')) {
            return 'show';
        }

        return 'edit';
    }

    /**
     * @return RouteHelperInterface
     */
    protected function getRouteHelper()
    {
        return app(RouteHelperInterface::class);
    }

}
