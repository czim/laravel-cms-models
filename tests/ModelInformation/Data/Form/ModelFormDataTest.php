<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form;

use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldGroupData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldsetData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormTabData;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormData;
use Czim\CmsModels\Support\Enums\LayoutNodeType;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelFormDataTest
 *
 * @group modelinformation-data
 */
class ModelFormDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_whether_layout_has_tabs()
    {
        $data = new ModelFormData;

        $data->layout = [];

        static::assertFalse($data->hasTabs());

        $data->layout = [
            'fielda',
            'fieldb',
        ];

        static::assertFalse($data->hasTabs());

        $data->layout = [
            [
                'type' => LayoutNodeType::TAB,
            ],
            'fielda',
        ];

        static::assertTrue($data->hasTabs());
    }

    /**
     * @test
     */
    function it_returns_tabs_from_layout()
    {
        $data = new ModelFormData;

        static::assertEmpty($data->tabs());

        $data->layout = [
            [
                'label' => 'tab-a',
                'type'  => LayoutNodeType::TAB,
            ],
            'fielda',
            [
                'label' => 'tab-b',
                'type'  => LayoutNodeType::TAB,
            ],
        ];

        static::assertCount(2, $data->tabs());
        static::assertInstanceOf(ModelFormTabData::class, head($data->tabs()));
        static::assertInstanceOf(ModelFormTabData::class, last($data->tabs()));
    }

    /**
     * @test
     */
    function it_returns_list_of_field_keys_if_layout_is_not_set()
    {
        $data = new ModelFormData;

        $data->fields = [
            'field_a' => [
                'source' => 'field_a',
            ],
            'field_b' => [
                'source' => 'field_b',
            ],
        ];

        static::assertEquals(['field_a', 'field_b'], $data->layout());
    }

    /**
     * @test
     */
    function it_returns_set_layout()
    {
        $data = new ModelFormData;

        $data->layout = [
            [
                'label' => 'tab-a',
                'type'  => LayoutNodeType::TAB,
            ],
            'field_a',
            'field_b',
        ];

        static::assertInternalType('array', $data->layout());
        static::assertCount(3, $data->layout());
    }

    /**
     * @test
     */
    function it_returns_nested_field_keys_from_layout()
    {
        $data = new ModelFormData;

        $data->layout = [
            [
                'label' => 'tab-a',
                'type'  => LayoutNodeType::TAB,
                'children' => [
                    'field_c',
                    [
                        'type'  => LayoutNodeType::GROUP,
                        'children' => [
                            'field_d',
                            'field_a', // should not report duplicates
                        ],
                    ]
                ]
            ],
            'field_a',
            'field_b',
        ];

        $keys = $data->getLayoutFormFieldKeys();
        sort($keys);

        static::assertEquals(['field_a','field_b','field_c', 'field_d'], $keys);
    }

    /**
     * @test
     */
    function it_decorates_layout_children()
    {
        $data = new ModelFormData;

        $data->layout = [
            'tab' => [
                'type'  => LayoutNodeType::TAB,
            ],
            'group' => [
                'type'  => LayoutNodeType::GROUP,
            ],
            'fieldset' => [
                'type'  => LayoutNodeType::FIELDSET,
            ],
        ];

        $layout = $data->layout;

        static::assertInstanceOf(ModelFormTabData::class, $layout['tab']);
        static::assertInstanceOf(ModelFormFieldGroupData::class, $layout['group']);
        static::assertInstanceOf(ModelFormFieldsetData::class, $layout['fieldset']);
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_it_cannot_decorate_a_layout_child()
    {
        $data = new ModelFormData;

        $data->layout = [
            'tab' => [
                'type' => 'unknown',
            ],
        ];

        $data->layout;
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelFormData;

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

        $with = new ModelFormData;

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
