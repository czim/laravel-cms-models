<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichFormLayoutData;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

class EnrichFormLayoutDataTest extends TestCase
{

    /**
     * @test
     */
    function it_derives_required_status_for_parents_of_required_children()
    {
        $mockEnricher = $this->getMockEnricher();

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        // Hidden attributes and foreign key columns should not be included
        $info->form->layout = [
            'tab-a' => [
                'type'     => 'tab',
                'label'    => 'Tab Pane A',
                'children' => [
                    [
                        'type'      => 'group',
                        'label'     => 'Testing',
                        'label_for' => 'checked',
                        'columns'   => [1, 2, 4, 1],
                        'children'  => [
                            'checked',
                            'checked_update',
                            [
                                'type'             => 'label',
                                'label_translated' => 'testing.label',
                                'required'         => false,
                                'label_for'        => 'author_name',
                            ],
                            'author_name',
                        ],
                    ],
                    'comments',
                    'seo',
                    'location',
                    'tagging',
                    'properties',
                ],
            ],
            'tab-b' => [
                'type'     => 'tab',
                'label'    => 'Tab Pane B',
                'children' => [
                    'testing'
                ],
            ]
        ];

        $info->form->fields = [
            'author_name' => new ModelFormFieldData([
                'key'      => 'author_name',
                'required' => true,
            ]),
            'testing' => new ModelFormFieldData([
                'key'      => 'testing',
                'required' => false,
            ]),
        ];

        $step = new EnrichFormLayoutData($mockEnricher);
        $step->enrich($info, []);

        static::assertTrue($info->form->layout['tab-a']->required);
        static::assertTrue($info->form->layout['tab-a']->children[0]->required);
        static::assertFalse($info->form->layout['tab-a']->children[0]->children[2]->required);
        static::assertNotTrue($info->form->layout['tab-b']->required);
    }

    /**
     * @test
     */
    function it_enriches_an_exception_with_dot_notation_nested_layout_key_context()
    {
        $mockEnricher = $this->getMockEnricher();

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        // Hidden attributes and foreign key columns should not be included
        $info->form->layout = [
            'tab-a' => [
                'type'     => 'tab',
                'label'    => 'Tab Pane A',
                'children' => [
                    [
                        'type'      => 'group',
                        'label'     => 'Testing',
                        'label_for' => 'checked',
                        'children'  => [
                            [
                                'type'             => 'label',
                                'label_translated' => 'testing.label',
                                'required'         => false,
                                'label_for'        => 'author_name',
                                'test'             => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $step = new EnrichFormLayoutData($mockEnricher);

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelConfigurationDataException $e */
            static::assertInstanceOf(ModelConfigurationDataException::class, $e);
            static::assertEquals('layout.children.tab-a.children.0.test', $e->getDotKey());
        }
    }

    /**
     * @return ModelInformationEnricherInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockEnricher()
    {
        return Mockery::mock(ModelInformationEnricherInterface::class);
    }

}
