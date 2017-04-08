<?php
namespace Czim\CmsModels\Test\Support\Factories;

use Czim\CmsModels\Strategies\Export\CsvExportStrategy;
use Czim\CmsModels\Support\Enums\ExportStrategy;
use Czim\CmsModels\Support\Factories\ExportStrategyFactory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ExportStrategyFactoryTest
 *
 * @group support
 * @group support-factories
 */
class ExportStrategyFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_strategy_by_alias()
    {
        $factory = new ExportStrategyFactory;

        static::assertInstanceOf(CsvExportStrategy::class, $factory->make(ExportStrategy::CSV));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_class_basename()
    {
        $factory = new ExportStrategyFactory;

        static::assertInstanceOf(CsvExportStrategy::class, $factory->make('CsvExportStrategy'));
    }

    /**
     * @test
     */
    function it_makes_a_strategy_by_full_class_name()
    {
        $factory = new ExportStrategyFactory;

        static::assertInstanceOf(CsvExportStrategy::class, $factory->make(CsvExportStrategy::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #no or unknown strategy given for exporting#i
     */
    function it_throws_an_exception_if_strategy_indicator_is_empty()
    {
        $factory = new ExportStrategyFactory;

        $factory->make('');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp #no or unknown strategy given for exporting#i
     */
    function it_throws_an_exception_if_strategy_could_not_be_resolved()
    {
        $factory = new ExportStrategyFactory;

        $factory->make('does-not-exist');
    }

}
