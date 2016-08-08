<?php
namespace Czim\CmsModels\Support\Routing;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;

/**
 * Class RouteHelper
 *
 * Route names for model controllers are built up as follows:
 *
 *      cms name prefix +
 *      model module name prefix +
 *      'models.' +
 *      model fqn slugged with dashes +
 *      .create / .index, etc
 *
 * This helper interprets and creates routes and names according to this pattern.
 */
class RouteHelper implements RouteHelperInterface
{
    const MODEL_ROUTE_NAME_PREFIX = 'model.';


    /**
     * Returns the model module key for the current route.
     *
     * @return false|string     false if this is not a model module route
     */
    public function getModuleKeyForCurrentRoute()
    {
        return $this->getModelModuleKeyForRouteNameSegment(
            $this->getModelRouteNameSegment()
        );
    }

    /**
     * Returns the model module key by a given route name.
     *
     * @param string $routeName
     * @return false|string     false if the name is not for a module route
     */
    public function getModuleKeyForRoute($routeName)
    {
        return $this->getModelModuleKeyForRouteNameSegment(
            $this->getModelRouteNameSegment($routeName)
        );
    }

    /**
     * Returns the route name for a given set of model information.
     *
     * @param ModelInformationInterface $information
     * @param bool                      $prefix
     * @return string
     */
    public function getRouteNameForModelInformation(ModelInformationInterface $information, $prefix = false)
    {
        if ( ! $information->modelClass()) {
            throw new \UnexpectedValueException("No model class in information, cannot make route name");
        }

        return $this->getRouteNameForModelClass($information->modelClass(), $prefix);
    }

    /**
     * Returns the route name for a given model FQN.
     *
     * @param string $modelClass
     * @param bool   $prefix        whether to include CMS & model module prefixes
     * @return string
     */
    public function getRouteNameForModelClass($modelClass, $prefix = false)
    {
        $modelSlug = static::MODEL_ROUTE_NAME_PREFIX
                   . $this->getRouteSlugForModelClass($modelClass);

        if ( ! $prefix) {
            return $modelSlug;
        }

        return config('cms-core.route.name-prefix')
             . config('cms-models.route.name-prefix')
             . $modelSlug;
    }

    /**
     * Returns the route path for a given set of model information.
     *
     * @param ModelInformationInterface $information
     * @param bool                      $prefix
     * @return string
     */
    public function getRoutePathForModelInformation(ModelInformationInterface $information, $prefix = false)
    {
        if ( ! $information->modelClass()) {
            throw new \UnexpectedValueException("No model class in information, cannot make route path");
        }

        return $this->getRoutePathForModelClass($information->modelClass(), $prefix);
    }

    /**
     * Returns the route path for a given model FQN.
     *
     * @param string $modelClass
     * @param bool   $prefix        whether to include CMS & model module prefixes
     * @return string
     */
    public function getRoutePathForModelClass($modelClass, $prefix = false)
    {
        $modelSlug = $this->getRouteSlugForModelClass($modelClass);

        if ( ! $prefix) {
            return $modelSlug;
        }

        return config('cms-core.route.prefix')
             . config('cms-models.route.prefix')
             . $modelSlug;
    }


    /**
     * Returns sluggified version of a model class FQN.
     *
     * @param string $modelClass FQN for model
     * @return string
     */
    public function getRouteSlugForModelClass($modelClass)
    {
        return strtolower(str_replace('\\', '-', $modelClass));
    }

    /**
     * Returns the model module key for a model route name segment.
     *
     * @param string $nameSegment
     * @return string
     */
    protected function getModelModuleKeyForRouteNameSegment($nameSegment)
    {
        $dotPosition = strpos($nameSegment, '.');

        if (false === $dotPosition) {
            return $nameSegment;
        }

        return substr($nameSegment, 0, $dotPosition);
    }

    /**
     * Returns the route name part that represents the model.
     *
     * @param string|null $routeName    if not set, uses current route
     * @return false|string
     */
    protected function getModelRouteNameSegment($routeName = null)
    {
        $routeName = $routeName ?: $this->getRouteName();

        $combinedPrefix = config('cms-core.route.name-prefix')
                        . config('cms-models.route.name-prefix')
                        . static::MODEL_ROUTE_NAME_PREFIX;

        if ( ! starts_with($routeName, $combinedPrefix)) {
            return false;
        }

        return substr($routeName, strlen($combinedPrefix));
    }

    /**
     * Returns the name of the current route.
     *
     * @return string
     */
    protected function getRouteName()
    {
        return request()->route()->getName();
    }

}
