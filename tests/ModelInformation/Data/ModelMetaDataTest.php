<?php
namespace Czim\CmsModels\Test\ModelInformation\Data;

use Czim\CmsModels\ModelInformation\Data\ModelMetaData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelMetaDataTest
 *
 * @group modelinformation-data
 */
class ModelMetaDataTest extends TestCase
{
    
    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelMetaData;

        $data->controller                     = 'TestController';
        $data->transformer                    = 'test';
        $data->repository_strategy_parameters = ['a'];
        $data->form_requests                  = ['create' => 'store'];
        $data->views                          = ['create' => 'some.view'];

        $with = new ModelMetaData;

        $with->controller                     = 'NewController';
        $with->transformer                    = 'new';
        $with->repository_strategy_parameters = ['b', 'c'];
        $with->form_requests                  = ['update' => 'edit'];
        $with->views                          = ['edit' => 'another.view'];

        $data->merge($with);

        static::assertEquals('NewController', $data->controller);
        static::assertEquals('new', $data->transformer);
        static::assertEquals(['b', 'c'], $data->repository_strategy_parameters);
        static::assertEquals(['create' => 'store', 'update' => 'edit'], $data->form_requests);
        static::assertEquals(['create' => 'some.view', 'edit' => 'another.view'], $data->views);
    }
    
}
