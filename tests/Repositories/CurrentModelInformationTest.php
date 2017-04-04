<?php
namespace Czim\CmsModels\Test\Repositories;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Repositories\CurrentModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class CurrentModelInformationTest
 *
 * @group repository
 */
class CurrentModelInformationTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_whether_the_current_request_is_for_a_model()
    {
        $infoRepositoryMock = $this->getMockModelInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(false);

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertFalse($repository->isForModel());

        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(true);
        $routeHelperMock->shouldReceive('getModuleKeyForCurrentRoute')->andReturn('testing');
        $infoRepositoryMock->shouldReceive('getByKey')->with('testing')
            ->andReturn(new ModelInformation([
                'model'          => TestPost::class,
                'original_model' => TestPost::class,
            ]));

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertTrue($repository->isForModel());
    }

    /**
     * @test
     */
    function it_returns_the_model_class_for_the_current_request()
    {
        $infoRepositoryMock = $this->getMockModelInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(false);

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertNull($repository->forModel());

        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(true);
        $routeHelperMock->shouldReceive('getModuleKeyForCurrentRoute')->andReturn('testing');
        $infoRepositoryMock->shouldReceive('getByKey')->with('testing')
            ->andReturn(new ModelInformation([
                'model'          => TestPost::class,
                'original_model' => TestPost::class,
            ]));

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertEquals(TestPost::class, $repository->forModel());
    }

    /**
     * @test
     */
    function it_returns_the_model_information_for_the_model_of_the_current_request()
    {
        $infoRepositoryMock = $this->getMockModelInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(false);

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertFalse($repository->info());

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(true);
        $routeHelperMock->shouldReceive('getModuleKeyForCurrentRoute')->andReturn('testing');
        $infoRepositoryMock->shouldReceive('getByKey')->with('testing')->andReturn($info);
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertSame($info, $repository->info());
    }

    /**
     * @test
     */
    function it_takes_a_model_to_fluently_access_information_for_another_model_than_the_requested()
    {
        $infoRepositoryMock = $this->getMockModelInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(false);

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertFalse($repository->info());

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);

        static::assertSame($repository, $repository->model(TestPost::class), 'Should allow fluent use');
        static::assertSame($info, $repository->info());
        static::assertFalse($repository->info(), 'Should have reset to default model after a single call');
        static::assertFalse($repository->model(TestPost::class)->model(null)->info(), 'Should allow manual reset for model');
    }

    /**
     * @test
     */
    function it_silently_accepts_missing_information_as_there_being_no_model()
    {
        $infoRepositoryMock = $this->getMockModelInfoRepository();
        $routeHelperMock    = $this->getMockRouteHelper();
        $routeHelperMock->shouldReceive('isModelRoute')->andReturn(true);
        $routeHelperMock->shouldReceive('getModuleKeyForCurrentRoute')->andReturn('testing');
        $infoRepositoryMock->shouldReceive('getByKey')->with('testing')->andReturn(null);

        $repository = new CurrentModelInformation($infoRepositoryMock, $routeHelperMock);

        static::assertFalse($repository->isForModel());
    }

    /**
     * @return ModelInformationRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModelInfoRepository()
    {
        return Mockery::mock(ModelInformationRepositoryInterface::class);
    }

    /**
     * @return RouteHelperInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockRouteHelper()
    {
        return Mockery::mock(RouteHelperInterface::class);
    }
}
