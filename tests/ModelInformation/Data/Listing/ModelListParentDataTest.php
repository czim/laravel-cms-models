<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Listing;

use Czim\CmsModels\ModelInformation\Data\Listing\ModelListParentData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelListParentDataTest
 *
 * @group modelinformation-data
 */
class ModelListParentDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_relation()
    {
        $data = new ModelListParentData;

        $data->relation = 'testing';

        static::assertEquals('testing', $data->relation());
    }

    /**
     * @test
     */
    function it_returns_field()
    {
        $data = new ModelListParentData;

        $data->field = false;

        static::assertFalse($data->field());

        $data->field = 'testing';

        static::assertEquals('testing', $data->field());
    }

    /**
     * @test
     */
    function it_defaults_to_relation_for_null_field()
    {
        $data = new ModelListParentData;

        $data->field    = null;
        $data->relation = 'testing';

        static::assertEquals('testing', $data->field());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelListParentData;

        $data->relation = 'test';
        $data->field    = 'field';

        $with = new ModelListParentData;

        $with->relation = 'replaced';
        $with->field    = 'replaced_field';

        $data->merge($with);

        static::assertEquals('replaced', $data->relation);
        static::assertEquals('replaced_field', $data->field);
    }

}
