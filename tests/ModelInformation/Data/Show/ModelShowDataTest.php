<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Show;

use Czim\CmsModels\ModelInformation\Data\Show\ModelShowData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelShowDataTest
 *
 * @group modelinformation-data
 */
class ModelShowDataTest extends TestCase
{

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelShowData;

        $data->before = [
            'view' => 'testing',
        ];
        $data->fields = [
            'a' => [
                'source' => 'a'
            ],
            'b' => [
                'source' => 'b'
            ],
        ];

        $with = new ModelShowData;

        $with->before = [
            'view' => 'replace',
        ];
        $with->fields = [
            'a' => [
                'source' => 'x'
            ],
            'c' => [
                'source' => 'y'
            ],
        ];

        $data->merge($with);

        static::assertEquals('replace', $data->before->view);
        static::assertEquals(['a', 'c'], array_keys($data->fields));
        static::assertEquals('x', $data->fields['a']->source);
        static::assertEquals('y', $data->fields['c']->source);
    }
    
}
