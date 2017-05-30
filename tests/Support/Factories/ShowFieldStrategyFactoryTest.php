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
    function it_makes_a_default_strategy_for_an_empty_value()
    {
        $factory = new ShowFieldStrategyFactory;

        static::assertInstanceOf(DefaultStrategy::class, $factory->make(null));
    }

    /**
     * @test
     */
    function it_uses_the_list_strategy_default_as_a_default_for_an_empty_value()
    {
        $this->app['config']->set('cms-models.strategies.show.default-strategy', null);

        $factory = new ShowFieldStrategyFactory;

        static::assertInstanceOf(DefaultStrategy::class, $factory->make(null));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_it_could_not_be_resolved()
    {
        $factory = new ShowFieldStrategyFactory;

        $factory->make('unresolvable');
    }

}
