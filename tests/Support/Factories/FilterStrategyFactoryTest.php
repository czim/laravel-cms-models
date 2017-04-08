<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
use Czim\CmsModels\Strategies\Filter\DropdownBoolean;
use Czim\CmsModels\Support\Enums\FilterStrategy;
use Czim\CmsModels\Support\Factories\FilterStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class FilterStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class FilterStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new FilterStrategyFactory;

        $info = new ModelListFilterData([
            'label' => 'Testing',
        ]);

        $filter = $factory->make(FilterStrategy::BOOLEAN, 'test', $info);

        static::assertInstanceOf(DropdownBoolean::class, $filter);
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new FilterStrategyFactory;

        static::assertInstanceOf(DropdownBoolean::class, $factory->make('DropdownBoolean'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new FilterStrategyFactory;

        static::assertInstanceOf(DropdownBoolean::class, $factory->make(DropdownBoolean::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not resolve filter strategy class#i
     */
    function it_throws_an_exception_if_strategy_indicator_is_empty()
    {
        $factory = new FilterStrategyFactory;

        $factory->make('');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #could not resolve filter strategy class#i
     */
    function it_throws_an_exception_if_strategy_could_not_be_resolved()
    {
        $factory = new FilterStrategyFactory;

        $factory->make('does-not-exist');
    }

}
