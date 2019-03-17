<?php
namespace Czim\CmsModels\Test\Support\Routing;

use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Routing\RouteHelper;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class RouteHelperTest
 *
 * @group support
 * @group support-helpers
 */
class RouteHelperTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('cms-core.route.name-prefix', 'cms::');
    }

    /**
     * @test
     */
    function it_returns_whether_the_current_route_is_for_a_model_when_it_is()
    {
        // At the root level
        /** @var RouteHelper|Mockery\Mock $helper */
        $helper = Mockery::mock(RouteHelper::class . '[getRouteName]')
            ->shouldAllowMockingProtectedMethods();

        $helper->shouldReceive('getRouteName')->andReturn('cms::models.model.test-model');

        static::assertTrue($helper->isModelRoute());

        // With descendant action
        $helper = Mockery::mock(RouteHelper::class . '[getRouteName]')
            ->shouldAllowMockingProtectedMethods();

        $helper->shouldReceive('getRouteName')->andReturn('cms::models.model.test-model.edit');

        static::assertTrue($helper->isModelRoute());
    }

    /**
     * @test
     */
    function it_returns_whether_the_current_route_is_for_a_model_when_it_is_not()
    {
        /** @var RouteHelper|Mockery\Mock $helper */
        $helper = Mockery::mock(RouteHelper::class . '[getRouteName]')
            ->shouldAllowMockingProtectedMethods();

        $helper->shouldReceive('getRouteName')->andReturn('test');

        static::assertFalse($helper->isModelRoute());
    }
    
    /**
     * @test
     */
    function it_returns_the_module_key_for_the_current_route()
    {
        /** @var RouteHelper|Mockery\Mock $helper */
        $helper = Mockery::mock(RouteHelper::class . '[getRouteName]')
            ->shouldAllowMockingProtectedMethods();

        $helper->shouldReceive('getRouteName')->andReturn('cms::models.model.test-model');

        static::assertEquals('models.test-model', $helper->getModuleKeyForCurrentRoute());
    }

    /**
     * @test
     */
    function it_returns_the_model_slug_for_the_current_route()
    {
        /** @var RouteHelper|Mockery\Mock $helper */
        $helper = Mockery::mock(RouteHelper::class . '[getRouteName]')
            ->shouldAllowMockingProtectedMethods();

        $helper->shouldReceive('getRouteName')->andReturn('cms::models.model.test-model');

        static::assertEquals('test-model', $helper->getModelSlugForCurrentRoute());
    }

    /**
     * @test
     */
    function it_returns_the_module_key_for_a_given_route()
    {
        $helper = new RouteHelper;

        static::assertEquals('models.test-model', $helper->getModuleKeyForRoute('cms::models.model.test-model'));
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_the_route_name_for_model_information()
    {
        $helper = new RouteHelper;

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        static::assertEquals(
            'model.czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRouteNameForModelInformation($info)
        );
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_the_route_name_for_model_information_with_a_prefix()
    {
        $helper = new RouteHelper;

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        static::assertEquals(
            'cms::models.model.czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRouteNameForModelInformation($info, true)
        );
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_throws_an_exception_if_it_cannot_return_the_route_name_for_model_information()
    {
        $helper = new RouteHelper;

        $info = new ModelInformation([
            'model'          => null,
            'original_model' => null,
        ]);

        $helper->getRouteNameForModelInformation($info);
    }

    /**
     * @test
     */
    function it_returns_the_route_name_for_a_model_class()
    {
        $helper = new RouteHelper;

        static::assertEquals(
            'model.czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRouteNameForModelClass(TestPost::class)
        );
    }

    /**
     * @test
     */
    function it_returns_the_route_name_for_a_model_class_with_prefix()
    {
        $helper = new RouteHelper;

        static::assertEquals(
            'cms::models.model.czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRouteNameForModelClass(TestPost::class, true)
        );
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_the_route_path_for_model_information()
    {
        $helper = new RouteHelper;

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        static::assertEquals(
            'czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRoutePathForModelInformation($info)
        );
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_the_route_path_for_model_information_with_a_prefix()
    {
        $helper = new RouteHelper;

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        static::assertEquals(
            'model/czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRoutePathForModelInformation($info, true)
        );
    }
    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_throws_an_exception_if_it_cannot_return_the_route_path_for_model_information()
    {
        $helper = new RouteHelper;

        $info = new ModelInformation([
            'model'          => null,
            'original_model' => null,
        ]);

        $helper->getRoutePathForModelInformation($info);
    }

    /**
     * @test
     */
    function it_returns_the_route_path_for_a_model_class()
    {
        $helper = new RouteHelper;

        static::assertEquals(
            'czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRoutePathForModelClass(TestPost::class)
        );
    }

    /**
     * @test
     */
    function it_returns_the_route_path_for_a_model_class_with_prefix()
    {
        $helper = new RouteHelper;

        static::assertEquals(
            'model/czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRoutePathForModelClass(TestPost::class, true)
        );
    }
    
    /**
     * @test
     */
    function it_returns_the_route_slug_for_a_model_class()
    {
        $helper = new RouteHelper;

        static::assertEquals(
            'czim-cmsmodels-test-helpers-models-testpost',
            $helper->getRouteSlugForModelClass(TestPost::class)
        );
    }

    /**
     * @test
     */
    function it_returns_the_permission_prefix_for_a_model_slug()
    {
        $helper = new RouteHelper;

        static::assertEquals(
            'models.testpost.',
            $helper->getPermissionPrefixForModelSlug('testpost')
        );
    }

    /**
     * @test
     */
    function it_returns_the_permission_prefix_for_a_model_module_key()
    {
        $helper = new RouteHelper;

        static::assertEquals(
            'models.testpost.',
            $helper->getPermissionPrefixForModuleKey('testpost')
        );

        static::assertEquals(
            'models.testpost.',
            $helper->getPermissionPrefixForModuleKey('models.testpost'),
            'Prefix was not stripped when included'
        );
    }

    /**
     * @test
     */
    function it_returns_the_permission_prefix_for_the_current_route()
    {
        /** @var RouteHelper|Mockery\Mock $helper */
        $helper = Mockery::mock(RouteHelper::class . '[getRouteName]')
            ->shouldAllowMockingProtectedMethods();

        $helper->shouldReceive('getRouteName')->andReturn('cms::models.model.test-model');

        static::assertEquals(
            'models.test-model.',
            $helper->getPermissionPrefixForCurrentRoute()
        );
    }

    /**
     * @test
     */
    function it_returns_false_for_permission_prefix_if_current_route_is_not_for_a_model()
    {
        /** @var RouteHelper|Mockery\Mock $helper */
        $helper = Mockery::mock(RouteHelper::class . '[getRouteName]')
            ->shouldAllowMockingProtectedMethods();

        $helper->shouldReceive('getRouteName')->andReturn('not-a-model-route');

        static::assertFalse($helper->getPermissionPrefixForCurrentRoute());
    }

}
