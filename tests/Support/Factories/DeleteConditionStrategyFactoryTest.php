<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Support\Factories\DeleteConditionStrategyFactory;
use Czim\CmsModels\Test\Helpers\Strategies\DeleteCondition\OnlyIfIdIsTwo;
use Czim\CmsModels\Test\TestCase;

/**
 * Class DeleteConditionStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class DeleteConditionStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new DeleteConditionStrategyFactory;

        $this->app['config']->set('cms-models.strategies.delete.condition-aliases', ['testing' => OnlyIfIdIsTwo::class]);

        static::assertInstanceOf(OnlyIfIdIsTwo::class, $factory->make('testing'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new DeleteConditionStrategyFactory;

        $this->app['config']
            ->set('cms-models.strategies.delete.default-condition-namespace', 'Czim\\CmsModels\\Test\\Helpers\\Strategies\\DeleteCondition\\');

        static::assertInstanceOf(OnlyIfIdIsTwo::class, $factory->make('OnlyIfIdIsTwo'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new DeleteConditionStrategyFactory;

        static::assertInstanceOf(OnlyIfIdIsTwo::class, $factory->make(OnlyIfIdIsTwo::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not create strategy instance#i
     */
    function it_throws_an_exception_if_strategy_indicator_is_empty()
    {
        $factory = new DeleteConditionStrategyFactory;

        $factory->make('');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not create strategy instance for 'does-not-exist'#i
     */
    function it_throws_an_exception_if_strategy_could_not_be_resolved()
    {
        $factory = new DeleteConditionStrategyFactory;

        $factory->make('does-not-exist');
    }

}
