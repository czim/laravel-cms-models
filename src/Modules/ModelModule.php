<?php
namespace Czim\CmsModels\Modules;

use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Support\Enums\AclPresenceType;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Routing\Router;
use UnexpectedValueException;

/**
 * Class ModelModule
 *
 * Template for standard models module.
 */
class ModelModule implements ModuleInterface
{
    const VERSION = '0.0.1';

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $version = self::VERSION;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ModelInformationRepositoryInterface
     */
    protected $repository;

    /**
     * @var ModuleHelperInterface
     */
    protected $moduleHelper;

    /**
     * @var RouteHelperInterface
     */
    protected $routeHelper;

    /**
     * The controller FQN for web requests.
     *
     * @var null|string
     */
    protected $webController;

    /**
     * The controller FQN for API requests.
     *
     * @var null|string
     */
    protected $apiController;


    /**
     * @param ModelInformationRepositoryInterface $repository
     * @param ModuleHelperInterface               $moduleHelper
     * @param RouteHelperInterface                $routeHelper
     * @param string                              $key
     * @param string|null                         $name
     */
    public function __construct(
        ModelInformationRepositoryInterface $repository,
        ModuleHelperInterface $moduleHelper,
        RouteHelperInterface $routeHelper,
        $key,
        $name = null
    ) {
        $this->repository   = $repository;
        $this->moduleHelper = $moduleHelper;
        $this->routeHelper  = $routeHelper;
        $this->key          = $key;
        $this->name         = $name;
    }

    /**
     * Sets the controller class to use for web requests.
     *
     * @param null|string $webController
     * @return $this
     */
    public function setWebController($webController)
    {
        $this->webController = $webController;

        return $this;
    }

    /**
     * Sets the controller class to use for API requests.
     *
     * @param null|string $apiController
     * @return $this
     */
    public function setApiController($apiController)
    {
        $this->apiController = $apiController;

        return $this;
    }

    /**
     * Returns unique identifying key for the module.
     * This should also be able to perform as a slug for it.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns display name for the module.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns release or version number of module.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the FQN for a class mainly associated with this module.
     *
     * @return string|null
     */
    public function getAssociatedClass()
    {
        return $this->class;
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
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router)
    {
        $permissionPrefix = $this->routeHelper->getPermissionPrefixForModuleKey($this->key);

        $router->group(
            [
                'prefix'    => $this->getRoutePrefix(),
                'as'        => $this->getRouteNamePrefix(),
                'middleware' => [ cms_mw_permission("{$permissionPrefix}*") ],
            ],
            function (Router $router) use ($permissionPrefix) {

                $controller = $this->getModelWebController();

                $router->get('/', [
                    'as'   => 'index',
                    'uses' => $controller . '@index',
                ]);

                $router->get('create', [
                    'as'         => 'create',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}create")],
                    'uses'       => $controller . '@create',
                ]);

                $router->post('/', [
                    'as'         => 'store',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}create")],
                    'uses'       => $controller . '@store',
                ]);

                $router->post('filter', [
                    'as'   => 'filter',
                    'uses' => $controller . '@filter',
                ]);

                $router->get('{key}', [
                    'as'   => 'show',
                    'uses' => $controller . '@show',
                ]);

                $router->get('{key}/edit', [
                    'as'         => 'edit',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}edit")],
                    'uses'       => $controller . '@edit',
                ]);

                $router->put('{key}/activate', [
                    'as'         => 'activate',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}edit")],
                    'uses'       => $controller . '@activate',
                ]);

                $router->put('{key}/position', [
                    'as'         => 'position',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}edit")],
                    'uses'       => $controller . '@position',
                ]);

                $router->get('{key}/deletable', [
                    'as'         => 'deletable',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}delete")],
                    'uses'       => $controller . '@deletable',
                ]);

                $router->put('{key}', [
                    'as'         => 'update',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}edit")],
                    'uses'       => $controller . '@update',
                ]);

                $router->delete('{key}', [
                    'as'         => 'destroy',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}delete")],
                    'uses'       => $controller . '@destroy',
                ]);
            }
        );
    }

    /**
     * Generates API routes for the module given a contextual router instance.
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapApiRoutes(Router $router)
    {
        $permissionPrefix = $this->routeHelper->getPermissionPrefixForModuleKey($this->key);

        $router->group(
            [
                'prefix'     => $this->getRoutePrefix(),
                'as'         => $this->getRouteNamePrefix(),
                'middleware' => [ cms_mw_permission("{$permissionPrefix}*") ],
            ],
            function (Router $router) use ($permissionPrefix) {

                $controller     = $this->getModelApiController();
                $permissionSlug = $this->getRouteSlug();

                $router->get('/', [
                    'as'   => 'index',
                    'uses' => $controller . '@index',
                ]);

                $router->get('create', [
                    'as'         => 'create',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}create")],
                    'uses'       => $controller . '@create',
                ]);

                $router->post('/', [
                    'as'         => 'store',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}create")],
                    'uses'       => $controller . '@store',
                ]);

                $router->get('{key}', [
                    'as'   => 'show',
                    'uses' => $controller . '@show',
                ]);

                $router->get('{key}/edit', [
                    'as'         => 'edit',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}edit")],
                    'uses'       => $controller . '@edit',
                ]);

                $router->put('{key}', [
                    'as'         => 'update',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}edit")],
                    'uses'       => $controller . '@update',
                ]);

                $router->delete('{key}', [
                    'as'         => 'destroy',
                    'middleware' => [cms_mw_permission("{$permissionPrefix}delete")],
                    'uses'       => $controller . '@destroy',
                ]);
            }
        );
    }

    /**
     * @return null|array|AclPresenceInterface|AclPresenceInterface[]
     */
    public function getAclPresence()
    {
        $slug = $this->getRouteSlug();

        return [
            [
                'id'          => 'models.' . $this->getRouteSlug(),
                'label'       => $this->getInformation()->labelPlural(),
                'type'        => AclPresenceType::GROUP,
                'permissions' => [
                    "models.{$slug}.show",
                    "models.{$slug}.create",
                    "models.{$slug}.edit",
                    "models.{$slug}.delete",
                ],
            ],
        ];
    }

    /**
     * Returns data for CMS menu presence.
     *
     * @return null|array|MenuPresenceInterface[]|MenuPresenceInterface[]
     */
    public function getMenuPresence()
    {
        return [
            'id'         => 'models.' . $this->getRouteSlug(),
            'label'      => $this->getInformation()->labelPlural(),
            'type'       => MenuPresenceType::ACTION,
            'action'     => $this->routeHelper->getRouteNameForModelClass($this->class, true) . '.index',
            'parameters' => [],
        ];
    }


    // ------------------------------------------------------------------------------
    //      Setters
    // ------------------------------------------------------------------------------

    /**
     * @param string $name
     * @return $this
     */
    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setAssociatedClass($class)
    {
        $this->class = $class;

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Information
    // ------------------------------------------------------------------------------

    /**
     * @return ModelInformation|false
     */
    protected function getInformation()
    {
        if ( ! ($information = $this->repository->getByModelClass($this->class))) {
            throw new UnexpectedValueException("No model information found for {$this->class}");
        }

        return $information;
    }


    // ------------------------------------------------------------------------------
    //      Routing Helpers
    // ------------------------------------------------------------------------------

    /**
     * Returns FQN of model controller for web request.
     *
     * @return string
     */
    protected function getModelWebController()
    {
        return $this->webController ?: config('cms-models.controllers.models.web');
    }

    /**
     * Returns FQN of model controller for API requests.
     *
     * @return string
     */
    protected function getModelApiController()
    {
        return $this->apiController ?: config('cms-models.controllers.models.api');
    }

    /**
     * @return string
     */
    protected function getRoutePrefix()
    {
        return config('cms-models.route.prefix') . '/'
             . $this->routeHelper->getRoutePathForModelClass($this->class);
    }

    /**
     * @return string
     */
    protected function getRouteNamePrefix()
    {
        return config('cms-models.route.name-prefix')
             . $this->routeHelper->getRouteNameForModelClass($this->class) . '.';
    }

    /**
     * @return string
     */
    protected function getRouteSlug()
    {
        return $this->routeHelper->getRouteSlugForModelClass($this->class);
    }

}
