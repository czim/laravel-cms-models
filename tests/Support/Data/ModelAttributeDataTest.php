<?php
namespace Czim\CmsModels\Test\Support\Data;

use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Test\TestCase;

class ModelAttributeDataTest extends TestCase
{

    /**
     * @test
     */
    function it_merges_with_another_model_attribute_data_object()
    {
        $dataA = new ModelAttributeData([
            'name'          => 'test',
            'cast'          => '',
            'type'          => '',
            'strategy'      => '',
            'strategy_form' => 'testStrategy',
            'strategy_list' => '',
            'fillable'      => false,
            'hidden'        => false,
            'translated'    => false,
            'length'        => 255,
            'nullable'      => true,
            'unsigned'      => false,
            'values'        => [],
        ]);

        $dataB = new ModelAttributeData([
            'name'          => 'testNew',
            'cast'          => 'string',
            'type'          => 'varchar',
            'strategy'      => 'testStrategy',
            'strategy_form' => 'testStrategyNew',
            'strategy_list' => 'listTestStrategy',
        ]);

        $dataA->merge($dataB);

        $this->assertEquals($dataA['name'], 'test');
        $this->assertEquals($dataA['cast'], 'string');
        $this->assertEquals($dataA['type'], 'varchar');
        $this->assertEquals($dataA['strategy'], 'testStrategy');
        $this->assertEquals($dataA['strategy_form'], 'testStrategy');
        $this->assertEquals($dataA['strategy_list'], 'listTestStrategy');
    }

    /**
     * @test
     */
    function it_merges_with_another_model_attribute_data_object_for_translation()
    {
        $dataA = new ModelAttributeData([
            'name'          => 'test',
            'cast'          => '',
            'type'          => '',
            'strategy'      => '',
            'strategy_form' => 'testStrategy',
            'strategy_list' => '',
            'fillable'      => false,
            'hidden'        => false,
            'translated'    => false,
            'length'        => 255,
            'nullable'      => true,
            'unsigned'      => false,
            'values'        => [],
        ]);

        $dataB = new ModelAttributeData([
            'name'          => 'testNew',
            'cast'          => 'string',
            'type'          => 'varchar',
            'strategy'      => 'testStrategy',
            'strategy_form' => 'testStrategyNew',
            'strategy_list' => 'listTestStrategy',
        ]);

        $dataA->mergeTranslation($dataB);

        $this->assertEquals($dataA['name'], 'test');
        $this->assertEquals($dataA['cast'], 'string');
        $this->assertEquals($dataA['type'], 'varchar');
        $this->assertEquals($dataA['strategy'], 'testStrategy');
        $this->assertEquals($dataA['strategy_form'], 'testStrategy');
        $this->assertEquals($dataA['strategy_list'], 'listTestStrategy');
    }

}
