<?php
namespace Czim\CmsModels\Test\Repositories\Criteria;

use Czim\CmsModels\Repositories\Criteria\ModelOrderStrategy;
use Czim\CmsModels\Strategies\Sort\ByKey;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Czim\Repository\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Mockery;

/**
 * Class ModelOrderStrategyTest
 *
 * @group repository
 * @group repository-criteria
 */
class ModelOrderStrategyTest extends TestCase
{

    /**
     * @test
     */
    function it_applies_a_specified_sort_strategy()
    {
        $queryMock      = $this->getQueryMock();
        $repositoryMock = $this->getRepositoryMock();

        $queryMock->shouldReceive('getModel')->andReturn(new TestPost);
        $queryMock->shouldReceive('orderBy')->with('test_posts.id', 'desc')->once()->andReturn($queryMock);

        $criteria = new ModelOrderStrategy(ByKey::class, null, 'desc');

        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

    /**
     * @test
     */
    function it_falls_back_to_default_sort_strategy_if_none_is_specified()
    {
        $queryMock      = $this->getQueryMock();
        $repositoryMock = $this->getRepositoryMock();

        $queryMock->shouldReceive('getModel')->andReturn(new TestPost);
        $queryMock->shouldReceive('orderBy')->with('test_posts.id', 'asc')->once()->andReturn($queryMock);

        $this->app['config']->set('cms-models.strategies.list.default-sort-strategy', ByKey::class);

        $criteria = new ModelOrderStrategy(null, null);

        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

    /**
     * @test
     */
    function it_uses_the_configured_default_sort_namespace_to_resolve_a_strategy()
    {
        $queryMock      = $this->getQueryMock();
        $repositoryMock = $this->getRepositoryMock();

        $queryMock->shouldReceive('getModel')->andReturn(new TestPost);
        $queryMock->shouldReceive('orderBy')->with('test_posts.id', 'asc')->once()->andReturn($queryMock);

        $this->app['config']->set('cms-models.strategies.list.default-sort-namespace', 'Czim\\CmsModels\\Strategies\\Sort\\');

        $criteria = new ModelOrderStrategy('ByKey', null);

        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

    /**
     * @test
     */
    function it_falls_back_to_simple_source_sort_if_no_strategy_could_be_resolved()
    {
        $queryMock      = $this->getQueryMock();
        $repositoryMock = $this->getRepositoryMock();

        $queryMock->shouldReceive('getModel')->andReturn(new TestPost);
        $queryMock->shouldReceive('orderBy')->with('title', 'asc')->once()->andReturn($queryMock);

        $this->app['config']->set('cms-models.strategies.list.default-sort-strategy', null);

        $criteria = new ModelOrderStrategy(null, 'title', 'asc');

        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

    /**
     * @return Builder|Mockery\Mock|Mockery\MockInterface
     */
    protected function getQueryMock()
    {
        return Mockery::mock(Builder::class);
    }

    /**
     * @return BaseRepositoryInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getRepositoryMock()
    {
        return Mockery::mock(BaseRepositoryInterface::class);
    }

}
