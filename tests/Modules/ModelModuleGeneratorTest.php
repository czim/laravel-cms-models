<?php
namespace Czim\CmsModels\Test\Modules;

use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Modules\ModelMetaModule;
use Czim\CmsModels\Modules\ModelModule;
use Czim\CmsModels\Modules\ModelModuleGenerator;
use Czim\CmsModels\Test\Helpers\Http\Controllers\TestController;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class ModelModuleGeneratorTest
 *
 * @group modules
 */
class ModelModuleGeneratorTest extends TestCase
{

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_generates_modules_based_on_modelinformation_in_the_repository()
    {
        /** @var ModelInformationRepositoryInterface|Mockery\Mock $infoRepository */
        /** @var ModuleHelperInterface|Mockery\Mock $moduleHelper */
        /** @var RouteHelperInterface|Mockery\Mock $routeHelper */
        $infoRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $moduleHelper   = Mockery::mock(ModuleHelperInterface::class);
        $routeHelper    = Mockery::mock(RouteHelperInterface::class);

        $infoRepository->shouldReceive('getAll')->andReturn(new Collection([
            'test-post'    => new ModelInformation([
                'model'          => TestPost::class,
                'original_model' => TestPost::class,
                'meta' => [
                    'controller_api' => TestController::class,
                ],
            ]),
            'test-comment' => new ModelInformation([
                'model'          => TestComment::class,
                'original_model' => TestComment::class,
                'meta'           => [
                    'controller' => TestController::class,
                ],
            ]),
        ]));

        $moduleHelper->shouldReceive('moduleKeyForModel')->with(TestPost::class)->andReturn('test-post');
        $moduleHelper->shouldReceive('moduleKeyForModel')->with(TestComment::class)->andReturn('test-comment');

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepository);
        $this->app->instance(RouteHelperInterface::class, $routeHelper);

        $generator = new ModelModuleGenerator($infoRepository, $moduleHelper);

        /** @var Collection|ModuleInterface[] $modules */
        $modules = $generator->modules()->values();

        static::assertInstanceOf(Collection::class, $modules);
        static::assertCount(3, $modules);

        static::assertInstanceOf(ModelMetaModule::class, $modules[0], 'First module should be the meta module');

        static::assertInstanceOf(ModelModule::class, $modules[1]);
        static::assertInstanceOf(ModelModule::class, $modules[2]);
        static::assertEquals(TestPost::class, $modules[1]->getAssociatedClass());
        static::assertEquals(TestComment::class, $modules[2]->getAssociatedClass());


        // Test whether custom controllers were set

        $moduleHelper->shouldReceive('modelSlug')->andReturn('test-model');
        $routeHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('test');
        $routeHelper->shouldReceive('getRoutePathForModelClass')->andReturn('models/test');
        $routeHelper->shouldReceive('getRouteNameForModelClass')->andReturn('models.test');

        $router = app(Router::class);

        $modules[1]->mapApiRoutes($router);
        /** @var Route $route */
        $route = $router->getRoutes()->getRoutes()[0];

        static::assertInstanceOf(TestController::class, $route->getController());

        $modules[2]->mapWebRoutes($router);
        /** @var Route $route */
        $route = $router->getRoutes()->getRoutes()[0];

        static::assertInstanceOf(TestController::class, $route->getController());
    }

}
