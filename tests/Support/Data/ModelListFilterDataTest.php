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
            'label' => 'some label',
            'label_translated' => null,
            'source' => 'column_a',
            'target' => 'column_b',
            'strategy' => 'testStrategy',
        ]);

        $dataB = new ModelListFilterData([
            'label' => 'some label new',
            'label_translated' => null,
            'source' => 'column_c',
            'target' => 'column_d',
            'strategy' => 'overruledStrategy',
            'values' => [
                'value_z',
            ],
        ]);

        $dataA->merge($dataB);

        $this->assertEquals('some label new', $dataA['label']);
        $this->assertEquals('column_c', $dataA['source']);
        $this->assertEquals('column_d', $dataA['target']);
        $this->assertEquals('overruledStrategy', $dataA['strategy']);
        $this->assertEquals([ 'value_z' ], $dataA['values']);
    }

    /**
     * @test
     */
    function it_merges_values_for_another_model_list_filter_data_object()
    {
        $dataA = new ModelListFilterData([
            'label' => 'some label',
            'label_translated' => null,
            'source' => 'column_a',
            'target' => 'column_b',
            'strategy' => 'testStrategy',
            'values' => [
                'value_x',
                'value_y',
            ],
        ]);

        $dataB = new ModelListFilterData([
            'values' => [
                'value_z',
            ],
        ]);

        $dataA->merge($dataB);

        $this->assertEquals([ 'value_x', 'value_y', 'value_z' ], $dataA['values']);
    }

}
