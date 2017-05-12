<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Strategies\Form\Store\BooleanStrategy;
use Czim\CmsModels\Strategies\Form\Store\DefaultStrategy;
use Czim\CmsModels\Support\Enums\FormStoreStrategy;
use Czim\CmsModels\Support\Factories\FormStoreStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class FormStoreStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class FormStoreStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new FormStoreStrategyFactory;

        static::assertInstanceOf(BooleanStrategy::class, $factory->make(FormStoreStrategy::BOOLEAN));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new FormStoreStrategyFactory;

        static::assertInstanceOf(BooleanStrategy::class, $factory->make('BooleanStrategy'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new FormStoreStrategyFactory;

        static::assertInstanceOf(BooleanStrategy::class, $factory->make(BooleanStrategy::class));
    }

    /**
     * @test
     */
    function it_makes_a_default_strategy_if_it_could_not_be_resolved()
    {
        $factory = new FormStoreStrategyFactory;

        static::assertInstanceOf(DefaultStrategy::class, $factory->make('unresolvable'));
    }

}
