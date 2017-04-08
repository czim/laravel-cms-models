<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Strategies\ListColumn\DateTime;
use Czim\CmsModels\Strategies\ListColumn\DefaultStrategy;
use Czim\CmsModels\Support\Enums\ListDisplayStrategy;
use Czim\CmsModels\Support\Factories\ListDisplayStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ListDisplayStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class ListDisplayStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new ListDisplayStrategyFactory;

        static::assertInstanceOf(DateTime::class, $factory->make(ListDisplayStrategy::DATETIME));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new ListDisplayStrategyFactory;

        static::assertInstanceOf(DateTime::class, $factory->make('DateTime'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new ListDisplayStrategyFactory;

        static::assertInstanceOf(DateTime::class, $factory->make(DateTime::class));
    }

    /**
     * @test
     */
    function it_makes_a_default_strategy_if_it_could_not_be_resolved()
    {
        $factory = new ListDisplayStrategyFactory;

        static::assertInstanceOf(DefaultStrategy::class, $factory->make('unresolvable'));
    }

}
