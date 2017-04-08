<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Strategies\Export\Column\BooleanStringStrategy;
use Czim\CmsModels\Strategies\Export\Column\DefaultStrategy;
use Czim\CmsModels\Support\Enums\ExportColumnStrategy;
use Czim\CmsModels\Support\Factories\ExportColumnStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ExportColumnStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class ExportColumnStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new ExportColumnStrategyFactory;

        static::assertInstanceOf(BooleanStringStrategy::class, $factory->make(ExportColumnStrategy::BOOLEAN_STRING));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new ExportColumnStrategyFactory;

        static::assertInstanceOf(BooleanStringStrategy::class, $factory->make('BooleanStringStrategy'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new ExportColumnStrategyFactory;

        static::assertInstanceOf(BooleanStringStrategy::class, $factory->make(BooleanStringStrategy::class));
    }

    /**
     * @test
     */
    function it_makes_a_default_strategy_if_it_could_not_be_resolved()
    {
        $factory = new ExportColumnStrategyFactory;

        static::assertInstanceOf(DefaultStrategy::class, $factory->make('unresolvable'));
    }

}
