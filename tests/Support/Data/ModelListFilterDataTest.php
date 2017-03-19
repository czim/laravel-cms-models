<?php
namespace Czim\CmsModels\Test\Support\Data;

use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Test\TestCase;

class ModelListFilterDataTest extends TestCase
{

    /**
     * @test
     */
    function it_merges_with_another_model_list_filter_data_object()
    {
        $dataA = new ModelListFilterData([
            'label'            => 'some label',
            'label_translated' => null,
            'source'           => 'column_a',
            'target'           => 'column_b',
            'strategy'         => 'testStrategy',
            'options'          => [
                'test' => 'something',
            ]
        ]);

        $dataB = new ModelListFilterData([
            'label'            => 'some label new',
            'label_translated' => null,
            'source'           => 'column_c',
            'target'           => 'column_d',
            'strategy'         => 'overruledStrategy',
            'options'          => [
                'values' => [
                    'value_z',
                ],
            ],
        ]);

        $dataA->merge($dataB);

        static::assertEquals('some label new', $dataA['label']);
        static::assertEquals('column_c', $dataA['source']);
        static::assertEquals('column_d', $dataA['target']);
        static::assertEquals('overruledStrategy', $dataA['strategy']);
        static::assertCount(2, $dataA['options']);
        static::assertEquals('something', $dataA['options']['test']);
        static::assertEquals([ 'value_z' ], $dataA['options']['values']);
    }

    /**
     * @test
     */
    function it_overrides_option_values_for_another_model_list_filter_data_object()
    {
        $dataA = new ModelListFilterData([
            'label' => 'some label',
            'label_translated' => null,
            'source' => 'column_a',
            'target' => 'column_b',
            'strategy' => 'testStrategy',
            'options' => [
                'values' => [
                    'value_x',
                    'value_y',
                ],
            ],
        ]);

        $dataB = new ModelListFilterData([
            'options' => [
                'values' => [
                    'value_z',
                ],
            ],
        ]);

        $dataA->merge($dataB);

        static::assertEquals([ 'value_z' ], $dataA['options']['values']);
    }

}
