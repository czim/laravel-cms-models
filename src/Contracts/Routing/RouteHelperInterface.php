<?php
namespace Czim\CmsModels\Contracts\Routing;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;

interface RouteHelperInterface
{

    /**
     * Returns the model module key for the current route.
     *
     * @return false|string     false if this is not a model module route
     */
    public function getModuleKeyForCurrentRoute();

    /**
     * Returns the model module key by a given route name.
     *
     * @param string $routeName
     * @return false|string     false if the name is not for a module route
     */
    public function getModuleKeyForRoute($routeName);

    /**
     * Returns the route name for a given set of model information.
     *
     * @param ModelInformationInterface $information
     * @param bool                      $prefix
     * @return string
     */
    public function getRouteNameForModelInformation(ModelInformationInterface $information, $prefix = false);

    /**
     * Returns the route name for a given model FQN.
     *
     * @param string $modelClass
     * @param bool   $prefix        whether to include CMS & model module prefixes
     * @return string
     */
    public function getRouteNameForModelClass($modelClass, $prefix = false);

    /**
     * Returns the route path for a given set of model information.
     *
     * @param ModelInformationInterface $information
     * @param bool                      $prefix
     * @return string
     */
    public function getRoutePathForModelInformation(ModelInformationInterface $information, $prefix = false);

    /**
     * Returns the route path for a given model FQN.
     *
     * @param string $modelClass
     * @param bool   $prefix        whether to include CMS & model module prefixes
     * @return string
     */
    public function getRoutePathForModelClass($modelClass, $prefix = false);

    /**
     * Returns sluggified version of a model class FQN.
     *
     * @param string $modelClass    FQN for model
     * @return string
     */
    public function getRouteSlugForModelClass($modelClass);

}
