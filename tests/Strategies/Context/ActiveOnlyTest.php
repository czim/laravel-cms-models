<?php
namespace Czim\CmsModels\Test\Strategies\Context;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\Context\ActiveOnly;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

/**
 * Class ActiveOnlyTest
 *
 * @group strategies
 * @group strategies-context
 */
class ActiveOnlyTest extends TestCase
{

    /**
     * @test
     */
    function it_applies_active_column_to_a_query()
    {
        $model = new TestPost;

        $info = new ModelInformation([
            'list' => [
                'activatable'   => true,
                'active_column' => 'checked',
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')->with('checked', true)->once()->andReturnSelf();
        $queryMock->shouldReceive('getModel')->andReturn($model);

        $strategy = new ActiveOnly;

        static::assertSame($queryMock, $strategy->apply($queryMock, []));
    }

    /**
     * @test
     */
    function it_defaults_to_using_the_active_column()
    {
        $model = new TestPost;

        $info = new ModelInformation;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('where')->with('active', true)->once()->andReturnSelf();
        $queryMock->shouldReceive('getModel')->andReturn($model);

        $strategy = new ActiveOnly;

        static::assertSame($queryMock, $strategy->apply($queryMock, []));
    }

}
