<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form\Layout;

use Czim\CmsModels\ModelInformation\Data\Form\Layout\AbstractModelFormLayoutNodeData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldsetData;

/**
 * Class ModelFormFieldsetDataTest
 *
 * @group modelinformation-data
 */
class ModelFormFieldsetDataTest extends AbstractModelFormLayoutNodeDataTestCase
{

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelFormFieldsetData;

        $data->label    = 'fieldset';
        $data->required = true;

        $with = new ModelFormFieldsetData;

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
        return new ModelFormFieldsetData;
    }

}
