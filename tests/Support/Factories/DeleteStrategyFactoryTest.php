<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Support\Factories\DeleteStrategyFactory;
use Czim\CmsModels\Test\Helpers\Strategies\Delete\MockDeleteSpy;
use Czim\CmsModels\Test\TestCase;

/**
 * Class DeleteStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class DeleteStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new DeleteStrategyFactory;

        $this->app['config']->set('cms-models.strategies.delete.aliases', ['testing' => MockDeleteSpy::class]);

        static::assertInstanceOf(MockDeleteSpy::class, $factory->make('testing'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new DeleteStrategyFactory;

        $this->app['config']
            ->set('cms-models.strategies.delete.default-namespace', 'Czim\\CmsModels\\Test\\Helpers\\Strategies\\Delete\\');

        static::assertInstanceOf(MockDeleteSpy::class, $factory->make('MockDeleteSpy'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new DeleteStrategyFactory;

        static::assertInstanceOf(MockDeleteSpy::class, $factory->make(MockDeleteSpy::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not create strategy instance#i
     */
    function it_throws_an_exception_if_strategy_indicator_is_empty()
    {
        $factory = new DeleteStrategyFactory;

        $factory->make('');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not create strategy instance for 'does-not-exist'#i
     */
    function it_throws_an_exception_if_strategy_could_not_be_resolved()
    {
        $factory = new DeleteStrategyFactory;

        $factory->make('does-not-exist');
    }

}
