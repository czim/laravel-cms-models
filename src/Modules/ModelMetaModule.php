<?php
namespace Czim\CmsModels\Modules;

use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Illuminate\Routing\Router;

/**
 * Class ModelMetaModule
 *
 * Meta functionality for the CMS models package.
 */
class ModelMetaModule implements ModuleInterface
{
    const VERSION = '0.0.1';


    /**
     * Returns unique identifying key for the module.
     * This should also be able to perform as a slug for it.
     *
     * @return string
     */
    public function getKey()
    {
        return 'models-meta';
    }

    /**
     * Returns display name for the module.
     *
     * @return string
     */
    public function getName()
    {
        return 'Models Meta Module';
    }

    /**
     * Returns release or version number of module.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return static::VERSION;
    }

    /**
     * Returns the FQN for a class mainly associated with this module.
     *
     * @return string|null
     */
    public function getAssociatedClass()
    {
        return null;
    }

    /**
     * Returns a list of FQNs for service providers that should always be registered.
     *
     * @return string[]
     */
    public function getServiceProviders()
    {
        return [];
    }

    /**
     * Generates web routes for the module given a contextual router instance.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router)
    {
        $router->group(
            [
                'prefix' => $this->getRoutePrefix(),
                'as'     => $this->getRouteNamePrefix(),
            ],
            function (Router $router) {

                $controller = $this->getModelWebController();

                $router->post('references', [
                    'as'   => 'references',
                    'uses' => $controller . '@references',
                ]);
            }
        );
    }

    /**
     * Generates API routes for the module given a contextual router instance.
     *
     * @param Router $router
     */
    public function mapApiRoutes(Router $router)
    {
        $router->group(
            [
                'prefix' => $this->getRoutePrefix(),
                'as'     => $this->getRouteNamePrefix(),
            ],
            function (Router $router) {

                $controller = $this->getModelApiController();

                $router->post('references', [
                    'as'   => 'references',
                    'uses' => $controller . '@references',
                ]);
            }
        );
    }

    /**
     * @return null|array|AclPresenceInterface|AclPresenceInterface[]
     */
    public function getAclPresence()
    {
        return null;
    }

    /**
     * Returns data for CMS menu presence.
     *
     * @return null|array|MenuPresenceInterface[]|MenuPresenceInterface[]
     */
    public function getMenuPresence()
    {
        return null;
    }


    // ------------------------------------------------------------------------------
    //      Routing Helpers
    // ------------------------------------------------------------------------------

    /**
     * Returns FQN of model meta controller for web request.
     *
     * @return string
     */
    protected function getModelWebController()
    {
        return config('cms-models.controllers.meta.web');
    }

    /**
     * Returns FQN of model meta controller for API requests.
     *
     * @return string
     */
    protected function getModelApiController()
    {
        return config('cms-models.controllers.meta.api');
    }

    /**
     * @return string
     */
    protected function getRoutePrefix()
    {
        return config('cms-models.route.meta.prefix');
    }

    /**
     * @return string
     */
    protected function getRouteNamePrefix()
    {
        return config('cms-models.route.meta.name-prefix');
    }

}
