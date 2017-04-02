<?php
namespace Czim\CmsModels\Test\ModelInformation\Data;

use Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelActionReferenceDataTest
 *
 * @group modelinformation-data
 */
class ModelActionReferenceDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_the_strategy()
    {
        $data = new ModelActionReferenceData;

        $data->strategy = 'testing';

        static::assertEquals('testing', $data->strategy());
    }

    /**
     * @test
     */
    function it_returns_permissions_as_array()
    {
        $data = new ModelActionReferenceData;

        $data->permissions = null;

        static::assertEquals([], $data->permissions());

        $data->permissions = 'testing';

        static::assertEquals(['testing'], $data->permissions());

        $data->permissions = ['testing', 'more'];

        static::assertEquals(['testing', 'more'], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_options_as_array()
    {
        $data = new ModelActionReferenceData;

        $data->options = null;

        static::assertEquals([], $data->options());

        $data->options = ['a' => 'x'];

        static::assertEquals(['a' => 'x'], $data->options());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelActionReferenceData;

        $data->strategy = 'test';
        $data->options  = ['a' => 'x'];

        $with = new ModelActionReferenceData;

        $with->strategy = 'replace';
        $with->options  = ['b' => 'y'];

        $data->merge($with);

        static::assertEquals('replace', $data->strategy);
        static::assertEquals(['b' => 'y'], $data->options);
    }
    
}
