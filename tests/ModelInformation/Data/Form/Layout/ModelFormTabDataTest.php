<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form\Layout;

use Czim\CmsModels\ModelInformation\Data\Form\Layout\AbstractModelFormLayoutNodeData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormTabData;

/**
 * Class ModelFormTabDataTest
 *
 * @group modelinformation-data
 */
class ModelFormTabDataTest extends AbstractModelFormLayoutNodeDataTestCase
{

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelFormTabData;

        $data->label    = 'tab';
        $data->required = true;

        $with = new ModelFormTabData;

        $data->label    = 'replace';
        $data->required = false;

        $data->merge($with);

        static::assertEquals('replace', $data->label);
        static::assertFalse($data->required);
    }

    /**
     * @return AbstractModelFormLayoutNodeData
     */
    protected function makeDataObject()
    {
        return new ModelFormTabData;
    }

}
