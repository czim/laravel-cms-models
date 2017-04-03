<?php
namespace Czim\CmsModels\Test\Support\Data\Strategies;

use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelMetaReferenceTest
 *
 * @group support-data
 */
class ModelMetaReferenceTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_model()
    {
        $data = new ModelMetaReference;

        $data->model = 'test';

        static::assertEquals('test', $data->model());
    }

    /**
     * @test
     */
    function it_returns_strategy()
    {
        $data = new ModelMetaReference;

        $data->strategy = 'test';

        static::assertEquals('test', $data->strategy());
    }

    /**
     * @test
     */
    function it_returns_context_strategy()
    {
        $data = new ModelMetaReference;

        $data->context_strategy = 'test';

        static::assertEquals('test', $data->contextStrategy());
    }

    /**
     * @test
     */
    function it_returns_source()
    {
        $data = new ModelMetaReference;

        $data->source = 'test';

        static::assertEquals('test', $data->source());
    }

    /**
     * @test
     */
    function it_returns_target()
    {
        $data = new ModelMetaReference;

        $data->source = 'source';

        static::assertEquals('source', $data->target());

        $data->target = 'test';

        static::assertEquals('test', $data->target());
    }

    /**
     * @test
     */
    function it_returns_parameters_as_array()
    {
        $data = new ModelMetaReference;

        static::assertEquals([], $data->parameters());

        $data->parameters = ['a'];

        static::assertEquals(['a'], $data->parameters());
    }
    
    /**
     * @test
     */
    function it_returns_sort_direction_as_asc_or_desc()
    {
        $data = new ModelMetaReference;

        static::assertEquals('asc', $data->sortDirection());

        $data->sort_direction = 0;

        static::assertEquals('asc', $data->sortDirection());

        $data->sort_direction = 'asc';

        static::assertEquals('asc', $data->sortDirection());

        $data->sort_direction = 'desc';

        static::assertEquals('desc', $data->sortDirection());
    }

    /**
     * @test
     */
    function it_returns_sort_strategy()
    {
        $data = new ModelMetaReference;

        $data->sort_strategy = 'test';

        static::assertEquals('test', $data->sortStrategy());
    }

}
