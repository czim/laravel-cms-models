<?php
namespace Czim\CmsModels\Test\Strategies\Action;

use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData;
use Czim\CmsModels\Strategies\Action\EditStrategy;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class EditStrategyTest
 *
 * @group strategies
 * @group strategies-action
 */
class EditStrategyTest extends AbstractActionStrategyTestCase
{

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     */
    function it_returns_an_edit_link_to_a_record()
    {
        $modelMock       = $this->getMockModel();
        $routeHelperMock = $this->getMockRouteHelper();

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestPost::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath/{id}', ['as' => 'testing.route.edit']);

        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);

        $strategy = new EditStrategy;

        $data = new ModelActionReferenceData([
            'strategy' => 'edit',
            'options'  => [],
        ]);

        $strategy->initialize($data, TestPost::class);

        static::assertEquals('http://localhost/somepath/1', $strategy->link($modelMock));
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData
     */
    function it_returns_an_edit_link_only_when_permitted_to()
    {
        $modelMock       = $this->getMockModel();
        $authMock        = $this->getMockAuthenticator();
        $routeHelperMock = $this->getMockRouteHelper();

        $modelMock->shouldReceive('getKey')->andReturn(1);
        $authMock->shouldReceive('can')->with(['testing.edit'])->andReturn(true, false);
        $routeHelperMock->shouldReceive('getRouteNameForModelClass')->with(TestPost::class, true)
            ->andReturn('testing.route');

        $this->app['router']->get('somepath/{id}', ['as' => 'testing.route.edit']);

        $this->app->instance(RouteHelperInterface::class, $routeHelperMock);
        $this->app->instance(Component::AUTH, $authMock);

        $strategy = new EditStrategy;

        $data = new ModelActionReferenceData([
            'strategy'    => 'edit',
            'permissions' => 'testing.edit',
            'options'     => [],
        ]);

        // First time should be allowed
        $strategy->initialize($data, TestPost::class);

        static::assertEquals('http://localhost/somepath/1', $strategy->link($modelMock), 'First call was not allowed');

        // Second time should NOT be allowed
        $strategy->initialize($data, TestPost::class);

        static::assertFalse($strategy->link($modelMock), 'Second call was allowed');
    }

}
