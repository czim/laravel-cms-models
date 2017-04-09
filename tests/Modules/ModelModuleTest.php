<?php
namespace Czim\CmsModels\Test\Modules;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Modules\ModelModule;
use Czim\CmsModels\Test\Helpers\Http\Controllers\TestController;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Mockery;

/**
 * Class ModelModuleTest
 *
 * @group modules
 */
class ModelModuleTest extends TestCase
{

    /**
     * @var ModelInformationRepositoryInterface|\Mockery\Mock
     */
    protected $infoRepository;

    /**
     * @var ModuleHelperInterface|\Mockery\Mock
     */
    protected $moduleHelper;

    /**
     * @var RouteHelperInterface|\Mockery\Mock
     */
    protected $routeHelper;

    /**
     * @test
     */
    function it_sets_the_associated_class()
    {
        $module = $this->makeModule();

        static::assertSame($module, $module->setAssociatedClass(TestPost::class));

        static::assertEquals(TestPost::class, $module->getAssociatedClass());
    }

    /**
     * @test
     */
    function it_returns_its_key()
    {
        $module = $this->makeModule();

        static::assertEquals('test-post', $module->getKey());
    }

    /**
     * @test
     */
    function it_returns_its_name()
    {
        $module = $this->makeModule();

        static::assertEquals('Test Post Module', $module->getName());
    }

    /**
     * @test
     */
    function it_returns_its_version()
    {
        $module = $this->makeModule();

        static::assertEquals(ModelModule::VERSION, $module->getVersion());
    }

    /**
     * @test
     */
    function it_returns_its_associated_class()
    {
        $module = $this->makeModule();

        static::assertEquals(TestPost::class, $module->getAssociatedClass());
    }

    /**
     * @test
     */
    function it_returns_its_service_providers()
    {
        $module = $this->makeModule();

        static::assertEquals([], $module->getServiceProviders());
    }

    /**
     * @test
     * @uses \Illuminate\Routing\Router
     */
    function it_maps_its_web_routes()
    {
        $router = app(Router::class);

        $module = $this->makeModule();

        $this->moduleHelper->shouldReceive('modelSlug')->with(TestPost::class)->andReturn('test-post');
        $this->routeHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('test-post');
        $this->routeHelper->shouldReceive('getRoutePathForModelClass')->with(TestPost::class)->andReturn('models/test-post');
        $this->routeHelper->shouldReceive('getRouteNameForModelClass')->andReturn('models.test-post');

        $module->mapWebRoutes($router);

        /** @var RouteCollection $routes */
        $routes = $router->getRoutes();
        static::assertCount(12, $routes);

        $uris = array_map(function (Route $route) { return $route->uri(); }, $routes->getRoutes());
        sort($uris);

        static::assertEquals(
            [
                'model/models/test-post',
                'model/models/test-post',
                'model/models/test-post/create',
                'model/models/test-post/export/{strategy}',
                'model/models/test-post/filter',
                'model/models/test-post/{key}',
                'model/models/test-post/{key}',
                'model/models/test-post/{key}',
                'model/models/test-post/{key}/activate',
                'model/models/test-post/{key}/deletable',
                'model/models/test-post/{key}/edit',
                'model/models/test-post/{key}/position',
            ],
            $uris
        );
    }

    /**
     * @test
     * @uses \Illuminate\Routing\Router
     */
    function it_maps_its_web_routes_using_a_set_custom_controller()
    {
        $router = app(Router::class);

        $module = $this->makeModule();

        $module->setWebController(TestController::class);

        $this->moduleHelper->shouldReceive('modelSlug')->with(TestPost::class)->andReturn('test-post');
        $this->routeHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('test-post');
        $this->routeHelper->shouldReceive('getRoutePathForModelClass')->with(TestPost::class)->andReturn('models/test-post');
        $this->routeHelper->shouldReceive('getRouteNameForModelClass')->andReturn('models.test-post');

        $module->mapWebRoutes($router);

        /** @var Route $route */
        $route = $router->getRoutes()->getRoutes()[0];

        static::assertInstanceOf(TestController::class, $route->getController());
    }

    /**
     * @test
     * @uses \Illuminate\Routing\Router
     */
    function it_maps_its_api_routes()
    {
        $router = app(Router::class);

        $module = $this->makeModule();

        $this->moduleHelper->shouldReceive('modelSlug')->with(TestPost::class)->andReturn('test-post');
        $this->routeHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('test-post');
        $this->routeHelper->shouldReceive('getRoutePathForModelClass')->with(TestPost::class)->andReturn('models/test-post');
        $this->routeHelper->shouldReceive('getRouteNameForModelClass')->andReturn('models.test-post');

        $module->mapApiRoutes($router);

        /** @var RouteCollection $routes */
        $routes = $router->getRoutes();
        static::assertCount(7, $routes);

        $uris = array_map(function (Route $route) { return $route->uri(); }, $routes->getRoutes());
        sort($uris);

        static::assertEquals(
            [
                'model/models/test-post',
                'model/models/test-post',
                'model/models/test-post/create',
                'model/models/test-post/{key}',
                'model/models/test-post/{key}',
                'model/models/test-post/{key}',
                'model/models/test-post/{key}/edit',
            ],
            $uris
        );
    }

    /**
     * @test
     * @uses \Illuminate\Routing\Router
     */
    function it_maps_its_api_routes_using_a_custom_controller()
    {
        $router = app(Router::class);

        $module = $this->makeModule();

        $module->setApiController(TestController::class);

        $this->moduleHelper->shouldReceive('modelSlug')->with(TestPost::class)->andReturn('test-post');
        $this->routeHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('test-post');
        $this->routeHelper->shouldReceive('getRoutePathForModelClass')->with(TestPost::class)->andReturn('models/test-post');
        $this->routeHelper->shouldReceive('getRouteNameForModelClass')->andReturn('models.test-post');

        $module->mapApiRoutes($router);

        /** @var Route $route */
        $route = $router->getRoutes()->getRoutes()[0];

        static::assertInstanceOf(TestController::class, $route->getController());
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_its_acl_presence()
    {
        $module = $this->makeModule();

        $this->routeHelper->shouldReceive('getRouteSlugForModelClass')
            ->with(TestPost::class)->andReturn('test-post');

        $this->infoRepository->shouldReceive('getByModelClass')->with(TestPost::class)
            ->andReturn(new ModelInformation([
                'model'               => TestPost::class,
                'original_model'      => TestPost::class,
                'verbose_name'        => 'TestPost',
                'verbose_name_plural' => 'TestPosts',
            ]));

        static::assertEquals(
            [
                [
                    'id'               => 'models.test-post',
                    'label'            => 'TestPosts',
                    'label_translated' => null,
                    'type'             => 'group',
                    'permissions'      => [
                        'models.test-post.show',
                        'models.test-post.create',
                        'models.test-post.edit',
                        'models.test-post.delete',
                        'models.test-post.export',
                    ],
                ],
            ],
            $module->getAclPresence()
        );
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_its_menu_presence()
    {
        $module = $this->makeModule();

        $this->routeHelper->shouldReceive('getRouteSlugForModelClass')
            ->with(TestPost::class)->andReturn('test-post');

        $this->routeHelper->shouldReceive('getRouteNameForModelClass')
            ->with(TestPost::class, Mockery::type('bool'))->andReturn('models.test-post');

        $this->infoRepository->shouldReceive('getByModelClass')->with(TestPost::class)
            ->andReturn(new ModelInformation([
                'model'               => TestPost::class,
                'original_model'      => TestPost::class,
                'verbose_name'        => 'TestPost',
                'verbose_name_plural' => 'TestPosts',
            ]));

        static::assertEquals(
            [
                'id'               => 'models.test-post',
                'label'            => 'TestPosts',
                'label_translated' => null,
                'type'             => 'action',
                'action'           => 'models.test-post.index',
                'parameters'       => ['home' => true],
                'permissions'      => ['models.test-post.*'],
            ],
            $module->getMenuPresence()
        );
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_it_cannot_find_model_information_for_the_class()
    {
        $module = $this->makeModule();

        $this->routeHelper->shouldReceive('getRouteSlugForModelClass')
            ->with(TestPost::class)->andReturn('test-post');

        $this->infoRepository->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn(null);

        $module->getMenuPresence();
    }


    /**
     * @return ModelModule
     */
    protected function makeModule()
    {
        $this->infoRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $this->moduleHelper   = Mockery::mock(ModuleHelperInterface::class);
        $this->routeHelper    = Mockery::mock(RouteHelperInterface::class);

        return (new ModelModule(
            $this->infoRepository,
            $this->moduleHelper,
            $this->routeHelper,
            'test-post',
            'Test Post Module'
        ))
            ->setAssociatedClass(TestPost::class);
    }

}
