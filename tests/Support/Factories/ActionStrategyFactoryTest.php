<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Strategies\Action\EditStrategy;
use Czim\CmsModels\Support\Factories\ActionStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ActionStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class ActionStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new ActionStrategyFactory;

        static::assertInstanceOf(EditStrategy::class, $factory->make('edit'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new ActionStrategyFactory;

        static::assertInstanceOf(EditStrategy::class, $factory->make('EditStrategy'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new ActionStrategyFactory;

        static::assertInstanceOf(EditStrategy::class, $factory->make(EditStrategy::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not create strategy instance#i
     */
    function it_throws_an_exception_if_strategy_indicator_is_empty()
    {
        $factory = new ActionStrategyFactory;

        $factory->make('');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not create strategy instance for 'does-not-exist'#i
     */
    function it_throws_an_exception_if_strategy_could_not_be_resolved()
    {
        $factory = new ActionStrategyFactory;

        $factory->make('does-not-exist');
    }

}
