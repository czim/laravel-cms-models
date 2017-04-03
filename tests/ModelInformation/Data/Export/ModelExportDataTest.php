<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Export;

use Czim\CmsModels\ModelInformation\Data\Export\ModelExportData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelExportDataTest
 *
 * @group modelinformation-data
 */
class ModelExportDataTest extends TestCase
{

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelExportData;

        $data->enable = true;
        $data->columns = [
            'a' => [
                'source' => 'a'
            ],
            'b' => [
                'source' => 'b'
            ],
        ];

        $with = new ModelExportData;

        $with->enable = false;
        $with->columns = [
            'a' => [
                'source' => 'x'
            ],
            'c' => [
                'source' => 'y'
            ],
        ];

        $data->merge($with);

        static::assertFalse($data->enable);
        static::assertEquals(['a', 'c'], array_keys($data->columns));
        static::assertEquals('x', $data->columns['a']->source);
        static::assertEquals('y', $data->columns['c']->source);
    }
    
}
