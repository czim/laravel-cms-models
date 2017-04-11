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
    function it_reports_that_it_should_display_if_it_has_children()
    {
        $data = new ModelFormTabData;

        $data->children = [
            'test',
            'test2',
        ];

        static::assertTrue($data->shouldDisplay());
    }

    /**
     * @test
     */
    function it_reports_that_it_should_display_if_it_has_before_or_after_view()
    {
        $data = new ModelFormTabData;

        $data->before = [
            'view' => 'test',
        ];

        static::assertTrue($data->shouldDisplay());

        $data = new ModelFormTabData;

        $data->after = [
            'view' => 'test',
        ];

        static::assertTrue($data->shouldDisplay());
    }

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
