<?php
namespace Czim\CmsModels\Test\Strategies\Action;

use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\Action\ChildrenStrategy;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestRelation;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Models\TestSeo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mockery;

/**
 * Class ChildrenStrategyTest
 *
 * @group strategies
 * @group strategies-action
 */
class ChildrenStrategyTest extends AbstractActionStrategyTestCase
{

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_an_children_link_to_a_record()
    {
        $modelMock          = $this->getMockModel();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $targetInfo = new ModelInformation;

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($targetInfo);

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestComment::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath', ['as' => 'testing.route.index']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model'    => TestComment::class,
                'relation' => 'post',
            ],
        ]);

        $strategy->initialize($data, TestPost::class);

        static::assertEquals('http://localhost/somepath?parent=post:1', $strategy->link($modelMock));
    }

    /**
     * @test
     */
    function it_defaults_to_target_model_list_parent_relation_if_there_is_only_one()
    {
        $modelMock          = $this->getMockModel();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $targetInfo = new ModelInformation([
            'list' => [
                'parents' => [
                    [
                        'relation' => 'post',
                    ],
                ],
            ]
        ]);

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($targetInfo);

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestComment::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath', ['as' => 'testing.route.index']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model' => TestComment::class,
            ],
        ]);

        $strategy->initialize($data, TestPost::class);

        static::assertEquals('http://localhost/somepath?parent=post:1', $strategy->link($modelMock));
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_an_children_link_to_a_record_only_if_permitted()
    {
        $modelMock          = $this->getMockModel();
        $authMock           = $this->getMockAuthenticator();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $targetInfo = new ModelInformation;

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($targetInfo);

        $authMock->shouldReceive('can')->with(['testing.show'])->andReturn(true, false);
        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestComment::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath', ['as' => 'testing.route.index']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);
        $this->app->instance(Component::AUTH, $authMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy'    => 'children',
            'permissions' => 'testing.show',
            'options'     => [
                'model'    => TestComment::class,
                'relation' => 'post',
            ],
        ]);

        // First time should be allowed
        $strategy->initialize($data, TestPost::class);

        static::assertNotFalse($strategy->link($modelMock));

        // Second time should NOT be allowed
        $strategy->initialize($data, TestPost::class);

        static::assertFalse($strategy->link($modelMock));
    }

    /**
     * @test
     */
    function it_returns_a_children_link_for_a_morph_to_relation()
    {
        $modelMock          = $this->getMockModel();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $targetInfo = new ModelInformation;

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestSeo::class)->andReturn($targetInfo);

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestSeo::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath', ['as' => 'testing.route.index']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model'    => TestSeo::class,
                'relation' => 'seoable',
            ],
        ]);

        $strategy->initialize($data, TestPost::class);

        static::assertEquals(
            'http://localhost/somepath?parent=seoable:Czim\CmsModels\Test\Helpers\Models\TestPost:1',
            $strategy->link($modelMock)
        );
    }

    /**
     * @test
     */
    function it_returns_a_children_link_for_a_morph_to_relation_when_using_a_morph_map()
    {
        Relation::morphMap([
            'test-post' => TestPost::class,
        ]);

        $modelMock          = $this->getMockModel();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $targetInfo = new ModelInformation;

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestSeo::class)->andReturn($targetInfo);

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestSeo::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath', ['as' => 'testing.route.index']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model'    => TestSeo::class,
                'relation' => 'seoable',
            ],
        ]);

        $strategy->initialize($data, TestPost::class);

        static::assertEquals(
            'http://localhost/somepath?parent=seoable:test-post:1',
            $strategy->link($modelMock)
        );
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #no target model class set#i
     */
    function it_throws_an_exception_if_no_other_model_class_is_defined()
    {
        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [],
        ]);

        $strategy->initialize($data, TestPost::class);
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #not a valid target model class#i
     */
    function it_throws_an_exception_if_an_invalid_model_class_is_defined()
    {
        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model' => static::class,
            ],
        ]);

        $strategy->initialize($data, TestPost::class);
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #TestComment is not a CMS model#i
     */
    function it_throws_an_exception_if_the_target_model_is_not_known_by_the_cms()
    {
        $modelMock          = $this->getMockModel();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn(false);

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestComment::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath/{id}', ['as' => 'testing.route.children']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model' => TestComment::class,
            ],
        ]);

        $strategy->initialize($data, TestPost::class);
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #could not determine relation#i
     */
    function it_throws_an_exception_if_no_parent_relation_is_set()
    {
        $modelMock          = $this->getMockModel();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $targetInfo = new ModelInformation;

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($targetInfo);

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestComment::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath/{id}', ['as' => 'testing.route.children']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model' => TestComment::class,
            ],
        ]);

        $strategy->initialize($data, TestPost::class);
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #is not a relation#i
     */
    function it_throws_an_exception_if_indicated_parent_relation_method_is_not_a_relation_method()
    {
        $modelMock          = $this->getMockModel();
        $infoRepositoryMock = $this->getMockInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();

        $targetInfo = new ModelInformation;

        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestRelation::class)->andReturn($targetInfo);

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestRelation::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath/{id}', ['as' => 'testing.route.children']);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);
        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new ChildrenStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'children',
            'options'  => [
                'model'    => TestRelation::class,
                'relation' => 'testIsNotARelationMethod',
            ],
        ]);

        $strategy->initialize($data, TestPost::class);
    }

    /**
     * @return ModelInformationRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockInfoRepository()
    {
        return Mockery::mock(ModelInformationRepositoryInterface::class);
    }

}
