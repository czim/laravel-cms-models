<?php
namespace Czim\CmsModels\Test\Strategies\Sort;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Strategies\SortStrategyInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\Sort\NullLast;
use Czim\CmsModels\Strategies\Sort\ReferenceResolvingRelay;
use Czim\CmsModels\Strategies\Sort\TranslatedAttribute;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ReferenceResolvingRelayTest
 *
 * @group strategies
 * @group strategies-sort
 */
class ReferenceResolvingRelayTest extends TestCase
{
    
    /**
     * @test
     */
    function it_silently_does_nothing_when_strategy_could_not_be_resolved()
    {
        $strategy = new ReferenceResolvingRelay;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repository */
        $repository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repository->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))
            ->andReturn(new ModelInformation);

        $this->app->instance(ModelInformationRepositoryInterface::class, $repository);

        $query = TestPost::query();

        $strategy->apply($query, 'unknown');

        static::assertRegExp('#select \* from [\'"`]?test_posts[\'"`]?#i', $query->toSql());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_a_resolved_sort_strategy_is_invalid()
    {
        $strategy = new ReferenceResolvingRelay;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repository */
        $repository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repository->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))
            ->andReturn(new ModelInformation([
                'attributes' => [
                    'type' => [
                        'name'       => 'type',
                        'translated' => false,
                    ],
                ]
            ]));

        $this->app->instance(ModelInformationRepositoryInterface::class, $repository);
        $this->app->instance(NullLast::class, 'not a strategy');

        $strategy->apply(TestPost::query(), 'type');
    }

    // ------------------------------------------------------------------------------
    //      Direct attribute
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_sorts_using_null_last_for_direct_attribute()
    {
        $strategy = new ReferenceResolvingRelay;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repository */
        $repository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repository->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))
            ->andReturn(new ModelInformation([
                'attributes' => [
                    'type' => [
                        'name'       => 'type',
                        'translated' => false,
                    ],
                ]
            ]));

        $this->app->instance(ModelInformationRepositoryInterface::class, $repository);

        /** @var SortStrategyInterface|Mockery\Mock $strategyMock */
        $strategyMock = Mockery::mock(SortStrategyInterface::class);
        $strategyMock->shouldReceive('apply')->once()->andReturnUsing(function ($query) { return $query; });
        $this->app->instance(NullLast::class, $strategyMock);

        $query = TestPost::query();

        static::assertSame($query, $strategy->apply($query, 'type'));
    }

    /**
     * @test
     */
    function it_sorts_using_null_last_for_direct_attribute_without_information_available()
    {
        $strategy = new ReferenceResolvingRelay;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repository */
        $repository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repository->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn(false);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repository);

        /** @var SortStrategyInterface|Mockery\Mock $strategyMock */
        $strategyMock = Mockery::mock(SortStrategyInterface::class);
        $strategyMock->shouldReceive('apply')->once()->andReturnUsing(function ($query) { return $query; });
        $this->app->instance(NullLast::class, $strategyMock);

        $query = TestPost::query();

        static::assertSame($query, $strategy->apply($query, 'type'));
    }


    // ------------------------------------------------------------------------------
    //      Translated attribute
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_sorts_using_translated_attribute_for_translated_attribute()
    {
        $strategy = new ReferenceResolvingRelay;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repository */
        $repository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repository->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))
            ->andReturn(new ModelInformation([
                'translated' => true,
                'attributes' => [
                    'title' => [
                        'name'       => 'title',
                        'translated' => true,
                    ],
                ]
            ]));

        $this->app->instance(ModelInformationRepositoryInterface::class, $repository);

        /** @var SortStrategyInterface|Mockery\Mock $strategyMock */
        $strategyMock = Mockery::mock(SortStrategyInterface::class);
        $strategyMock->shouldReceive('apply')->once()->andReturnUsing(function ($query) { return $query; });
        $this->app->instance(TranslatedAttribute::class, $strategyMock);

        $query = TestPost::query();

        static::assertSame($query, $strategy->apply($query, 'title'));
    }

    /**
     * @test
     */
    function it_sorts_using_translated_attribute_for_translated_attribute_without_information_available()
    {
        $strategy = new ReferenceResolvingRelay;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repository */
        $repository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repository->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn(false);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repository);

        /** @var SortStrategyInterface|Mockery\Mock $strategyMock */
        $strategyMock = Mockery::mock(SortStrategyInterface::class);
        $strategyMock->shouldReceive('apply')->once()->andReturnUsing(function ($query) { return $query; });
        $this->app->instance(TranslatedAttribute::class, $strategyMock);

        $query = TestPost::query();

        static::assertSame($query, $strategy->apply($query, 'title'));
    }

    // ------------------------------------------------------------------------------
    //      Attribute on related
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_sorts_using_related_attribute_for_dot_notation_column()
    {
        // todo
    }

}
