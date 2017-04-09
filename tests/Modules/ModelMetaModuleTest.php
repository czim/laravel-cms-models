<?php
namespace Czim\CmsModels\Test\Modules;

use Czim\CmsModels\Modules\ModelMetaModule;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

/**
 * Class ModelMetaModuleTest
 *
 * @group modules
 */
class ModelMetaModuleTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_its_key()
    {
        $module = new ModelMetaModule;

        static::assertEquals('models-meta', $module->getKey());
    }

    /**
     * @test
     */
    function it_returns_its_name()
    {
        $module = new ModelMetaModule;

        static::assertEquals('Models Meta Module', $module->getName());
    }

    /**
     * @test
     */
    function it_returns_its_version()
    {
        $module = new ModelMetaModule;

        static::assertEquals(ModelMetaModule::VERSION, $module->getVersion());
    }

    /**
     * @test
     */
    function it_returns_its_associated_class()
    {
        $module = new ModelMetaModule;

        static::assertNull($module->getAssociatedClass());
    }

    /**
     * @test
     */
    function it_returns_its_service_providers()
    {
        $module = new ModelMetaModule;

        static::assertEquals([], $module->getServiceProviders());
    }

    /**
     * @test
     * @uses \Illuminate\Routing\Router
     */
    function it_maps_its_web_routes()
    {
        $router = app(Router::class);

        $module = new ModelMetaModule;

        $module->mapWebRoutes($router);

        /** @var RouteCollection $routes */
        $routes = $router->getRoutes();
        static::assertCount(1, $routes);

        /** @var Route $route */
        $route = $routes->getRoutes()[0];
        static::assertEquals('models-meta/references', $route->uri());
    }

    /**
     * @test
     * @uses \Illuminate\Routing\Router
     */
    function it_maps_its_api_routes()
    {
        $router = app(Router::class);

        $module = new ModelMetaModule;

        $module->mapApiRoutes($router);

        /** @var RouteCollection $routes */
        $routes = $router->getRoutes();
        static::assertCount(1, $routes);

        /** @var Route $route */
        $route = $routes->getRoutes()[0];
        static::assertEquals('models-meta/references', $route->uri());
    }

    /**
     * @test
     */
    function it_returns_its_acl_presence()
    {
        $module = new ModelMetaModule;

        static::assertNull($module->getAclPresence());
    }

    /**
     * @test
     */
    function it_returns_its_menu_presence()
    {
        $module = new ModelMetaModule;

        static::assertNull($module->getMenuPresence());
    }

}
