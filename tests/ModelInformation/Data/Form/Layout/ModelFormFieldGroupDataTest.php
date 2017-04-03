<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form\Layout;

use Czim\CmsModels\ModelInformation\Data\Form\Layout\AbstractModelFormLayoutNodeData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldGroupData;
use Czim\CmsModels\Support\Enums\LayoutNodeType;

/**
 * Class ModelFormFieldGroupDataTest
 *
 * @group modelinformation-data
 */
class ModelFormFieldGroupDataTest extends AbstractModelFormLayoutNodeDataTestCase
{

    /**
     * @test
     */
    function it_returns_normalized_set_column_widths()
    {
        $data = new ModelFormFieldGroupData;

        $data->columns = [1, 3, 2];

        static::assertEquals([1, 3, 6], $data->columns());
    }

    /**
     * @test
     */
    function it_derives_column_widths_from_children()
    {
        $data = new ModelFormFieldGroupData;

        $data->children = [
            [
                'type'  => LayoutNodeType::LABEL,
                'label' => 'Test',
            ],
            'field_a',
            'field_b',
        ];

        static::assertEquals([2, 4, 4], $data->columns());

        $data->children = [
            'field_a',
            'field_b',
            'field_c',
        ];

        static::assertEquals([3, 3, 4], $data->columns());

        $data->children = [
            'field_a',
            [
                'type'  => LayoutNodeType::LABEL,
                'label' => 'Test',
            ],
            'field_b',
            'field_c',
        ];

        static::assertEquals([2, 2, 2, 4], $data->columns());
    }

    /**
     * @test
     */
    function it_returns_whether_any_of_given_keys_match_descendant_form_field_keys()
    {
        $data = new ModelFormFieldGroupData;

        $data->children = [
            'field_a',
            [
                'type'  => LayoutNodeType::LABEL,
                'label' => 'Test',
            ],
            'field_b',
            'field_c',
        ];

        static::assertFalse($data->matchesFieldKeys([]));
        static::assertFalse($data->matchesFieldKeys(['does_not_match']));
        static::assertTrue($data->matchesFieldKeys(['field_b']));
        static::assertTrue($data->matchesFieldKeys(['field_b', 'field_c']));
        static::assertTrue($data->matchesFieldKeys(['field_b', 'does_not_match']));
    }

    /**
     * @return AbstractModelFormLayoutNodeData
     */
    protected function makeDataObject()
    {
        return new ModelFormFieldGroupData;
    }

}
