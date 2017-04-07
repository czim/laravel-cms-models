<?php
namespace Czim\CmsModels\Test\Repositories\Criteria;

use Czim\CmsModels\Repositories\Criteria\ContextStrategy;
use Czim\CmsModels\Test\Helpers\Strategies\Context\TestSpecificIdOnly;
use Czim\CmsModels\Test\TestCase;
use Czim\Repository\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Mockery;

/**
 * Class ContextStrategyTest
 *
 * @group repository
 * @group repository-criteria
 */
class ContextStrategyTest extends TestCase
{

    /**
     * @test
     */
    function it_applies_a_specified_context_strategy()
    {
        $queryMock      = $this->getQueryMock();
        $repositoryMock = $this->getRepositoryMock();

        $queryMock->shouldReceive('where')->with('id', 2)->once()->andReturn($queryMock);

        $criteria = new ContextStrategy(TestSpecificIdOnly::class, ['a' => 'x']);

        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

    /**
     * @test
     */
    function it_applies_the_default_context_strategy_if_none_is_specified()
    {
        $queryMock      = $this->getQueryMock();
        $repositoryMock = $this->getRepositoryMock();

        $this->app['config']->set('cms-models.strategies.repository.default-strategy', TestSpecificIdOnly::class);

        $queryMock->shouldReceive('where')->with('id', 2)->once()->andReturn($queryMock);

        // When no default is given, and none is specified
        $criteria = new ContextStrategy(null);
        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));
    }

    /**
     * @test
     */
    function it_silently_ignores_unavailable_strategies()
    {
        $queryMock      = $this->getQueryMock();
        $repositoryMock = $this->getRepositoryMock();

        $this->app['config']->set('cms-models.strategies.repository.default-strategy', null);

        // When no default is given, and none is specified
        $criteria = new ContextStrategy(null);
        static::assertInstanceOf(Builder::class, $criteria->apply($queryMock, $repositoryMock));

        // When a specified strategy could not be resolved
        $criteria = new ContextStrategy('unresolvable');
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
