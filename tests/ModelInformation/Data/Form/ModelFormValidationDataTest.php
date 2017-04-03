<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form;

use Czim\CmsModels\ModelInformation\Data\Form\ModelFormValidationData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelFormValidationDataTest
 *
 * @group modelinformation-data
 */
class ModelFormValidationDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_shared_rules_as_array()
    {
        $data = new ModelFormValidationData;

        static::assertEquals([], $data->sharedRules());

        $data->shared = [
            'test' => 'required',
        ];

        static::assertEquals(['test' => 'required'], $data->sharedRules());
    }

    /**
     * @test
     */
    function it_returns_create_rules_as_array()
    {
        $data = new ModelFormValidationData;

        static::assertEquals([], $data->create());

        $data->create = [
            'test' => 'required',
        ];

        static::assertEquals(['test' => 'required'], $data->create());
    }

    /**
     * @test
     */
    function it_returns_update_rules_as_array()
    {
        $data = new ModelFormValidationData;

        static::assertEquals([], $data->update());

        $data->update = [
            'test' => 'required',
        ];

        static::assertEquals(['test' => 'required'], $data->update());
    }

    /**
     * @test
     */
    function it_returns_the_rules_class()
    {
        $data = new ModelFormValidationData;

        $data->rules_class = 'testing';

        static::assertEquals('testing', $data->rulesClass());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelFormValidationData;

        $data->shared = [
            'test' => 'required',
        ];
        $data->create = [
            'test' => 'required',
        ];
        $data->update = [
            'test' => 'required',
        ];

        $with = new ModelFormValidationData;

        $with->shared = [
            'alt' => 'required',
        ];
        $with->create = [
            'alt' => 'required',
        ];
        $with->update = [
            'alt' => 'required',
        ];

        $data->merge($with);

        static::assertEquals(['alt'], array_keys($data->shared));
        static::assertEquals(['alt'], array_keys($data->create));
        static::assertEquals(['alt'], array_keys($data->update));

        // It does not merge in empty rules
        $data = new ModelFormValidationData;

        $data->shared = [
            'test' => 'required',
        ];
        $data->create = [
            'test' => 'required',
        ];
        $data->update = [
            'test' => 'required',
        ];

        $with = new ModelFormValidationData;

        $with->shared = [];
        $with->create = [];
        $with->update = [];

        $data->merge($with);

        static::assertCount(1, $data->shared);
        static::assertCount(1, $data->create);
        static::assertCount(1, $data->update);
    }
    
}
