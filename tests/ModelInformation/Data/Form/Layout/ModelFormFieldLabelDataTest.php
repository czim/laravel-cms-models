<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form\Layout;

use Czim\CmsModels\ModelInformation\Data\Form\Layout\AbstractModelFormLayoutNodeData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldLabelData;

/**
 * Class ModelFormFieldLabelDataTest
 *
 * @group modelinformation-data
 */
class ModelFormFieldLabelDataTest extends AbstractModelFormLayoutNodeDataTestCase
{

    /**
     * @test
     */
    function it_returns_label_for()
    {
        $data = $this->makeDataObject();

        $data->label_for = 'for_key';

        static::assertEquals('for_key', $data->labelFor());
    }

    /**
     * @return AbstractModelFormLayoutNodeData
     */
    protected function makeDataObject()
    {
        return new ModelFormFieldLabelData;
    }

}
