<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Strategies\Form\Display\DateTimeStrategy;
use Czim\CmsModels\Strategies\Form\Display\DefaultStrategy;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\ListDisplayStrategy;
use Czim\CmsModels\Support\Factories\FormFieldStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class FormFieldStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class FormFieldStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new FormFieldStrategyFactory;

        static::assertInstanceOf(DateTimeStrategy::class, $factory->make(FormDisplayStrategy::DATEPICKER_DATETIME));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new FormFieldStrategyFactory;

        static::assertInstanceOf(DateTimeStrategy::class, $factory->make('DateTimeStrategy'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new FormFieldStrategyFactory;

        static::assertInstanceOf(DateTimeStrategy::class, $factory->make(DateTimeStrategy::class));
    }

    /**
     * @test
     */
    function it_makes_a_default_strategy_if_it_could_not_be_resolved()
    {
        $factory = new FormFieldStrategyFactory;

        static::assertInstanceOf(DefaultStrategy::class, $factory->make('unresolvable'));
    }

}
