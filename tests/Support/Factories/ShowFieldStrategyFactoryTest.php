<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Strategies\ListColumn\Check;
use Czim\CmsModels\Strategies\ListColumn\DefaultStrategy;
use Czim\CmsModels\Support\Factories\ShowFieldStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ShowFieldStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class ShowFieldStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new ShowFieldStrategyFactory;

        $this->app['config']->set('cms-models.strategies.show.aliases', [
            'test' => 'Check',
        ]);

        static::assertInstanceOf(Check::class, $factory->make('test'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new ShowFieldStrategyFactory;

        static::assertInstanceOf(Check::class, $factory->make('Check'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename_falling_back_to_list_namespace()
    {
        $factory = new ShowFieldStrategyFactory;

        $this->app['config']->set('cms-models.strategies.show.default-namespace', null);

        static::assertInstanceOf(Check::class, $factory->make('Check'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new ShowFieldStrategyFactory;

        static::assertInstanceOf(Check::class, $factory->make(Check::class));
    }

    /**
     * @test
     */
    function it_makes_a_default_strategy_if_it_could_not_be_resolved()
    {
        $factory = new ShowFieldStrategyFactory;

        static::assertInstanceOf(DefaultStrategy::class, $factory->make('unresolvable'));
    }

    /**
     * @test
     */
    function it_makes_a_default_strategy_if_it_could_not_be_resolved_falling_back_to_list_default()
    {
        $factory = new ShowFieldStrategyFactory;

        $this->app['config']->set('cms-models.strategies.show.default-strategy', null);

        static::assertInstanceOf(DefaultStrategy::class, $factory->make('unresolvable'));
    }

}
