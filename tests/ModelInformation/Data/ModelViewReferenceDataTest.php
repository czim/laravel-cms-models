<?php
namespace Czim\CmsModels\Test\ModelInformation\Data;

use Czim\CmsModels\ModelInformation\Data\ModelViewReferenceData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelViewReferenceDataTest
 *
 * @group modelinformation-data
 */
class ModelViewReferenceDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_the_view_partial()
    {
        $data = new ModelViewReferenceData;

        $data->view = 'testing';

        static::assertEquals('testing', $data->view());
    }

    /**
     * @test
     */
    function it_returns_variables_as_array()
    {
        $data = new ModelViewReferenceData;

        $data->variables = null;

        static::assertEquals([], $data->variables());

        $data->variables = ['a'];

        static::assertEquals(['a'], $data->variables());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelViewReferenceData;

        $data->view      = 'test';
        $data->variables = ['a', 'b'];

        $with = new ModelViewReferenceData;

        $with->view      = 'replace';
        $with->variables = ['b', 'c'];

        $data->merge($with);

        static::assertEquals('replace', $data->view);
        static::assertEquals(['a', 'b', 'c'], array_values($data->variables));
    }

}
