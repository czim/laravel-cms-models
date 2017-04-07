<?php
namespace Czim\CmsModels\Test\Repositories\Criteria;

use Czim\CmsModels\Repositories\Criteria\DisableGlobalScopes;
use Czim\CmsModels\Test\TestCase;
use Czim\Repository\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Mockery;

/**
 * Class DisableGlobalScopesTest
 *
 * @group repository
 * @group repository-criteria
 */
class DisableGlobalScopesTest extends TestCase
{

    /**
     * @test
     */
    function it_disables_all_global_scopes()
    {
        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);
        /** @var BaseRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(BaseRepositoryInterface::class);

        $queryMock->shouldReceive('withoutGlobalScopes')->with(null)->once()->andReturn($queryMock);

        $criteria = new DisableGlobalScopes(true);

        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

    /**
     * @test
     */
    function it_disables_specific_global_scopes()
    {
        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);
        /** @var BaseRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(BaseRepositoryInterface::class);

        $queryMock->shouldReceive('withoutGlobalScopes')->with(['a', 'b'])->once()->andReturn($queryMock);

        $criteria = new DisableGlobalScopes(['a', 'b']);

        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

}
