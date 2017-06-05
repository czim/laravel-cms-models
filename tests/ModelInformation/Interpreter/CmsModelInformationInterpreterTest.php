<?php
namespace Czim\CmsModels\Test\ModelInformation\Interpreter;

use Czim\CmsModels\Contracts\ModelInformation\Data\Export\ModelExportColumnDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Export\ModelExportStrategyDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelListColumnDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelListParentDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelScopeDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelActionReferenceDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Show\ModelShowFieldDataInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportColumnData;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportStrategyData;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldHelpData;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListColumnData;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListParentData;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelScopeData;
use Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\Show\ModelShowFieldData;
use Czim\CmsModels\ModelInformation\Interpreter\CmsModelInformationInterpreter;
use Czim\CmsModels\Test\TestCase;

/**
 * Class CmsModelInformationInterpreterTest
 *
 * @group collection
 */
class CmsModelInformationInterpreterTest extends TestCase
{

    // ------------------------------------------------------------------------------
    //      List
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_normalizes_list_default_actions()
    {
        $input = [
            'single' => true,
            'list'   => [
                'default_action' => [
                    'edit',
                    [
                        'strategy'    => \Czim\CmsModels\Support\Enums\ActionReferenceType::SHOW,
                        'permissions' => 'testing.permission',
                        'options'     => ['test' => true],
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(2, $output->list->default_action);

        /** @var ModelActionReferenceData $action */
        $action = head($output->list->default_action);
        static::assertInstanceOf(ModelActionReferenceDataInterface::class, $action);
        static::assertEquals('edit', $action->strategy);
        static::assertEmpty($action->permissions);
        static::assertEmpty($action->options);

        $action = last($output->list->default_action);
        static::assertInstanceOf(ModelActionReferenceDataInterface::class, $action);
        static::assertEquals(\Czim\CmsModels\Support\Enums\ActionReferenceType::SHOW, $action->strategy);
        static::assertEquals('testing.permission', $action->permissions);
        static::assertEquals(['test' => true], $action->options);
    }

    /**
     * @test
     */
    function it_normalizes_list_columns()
    {
        $input = [
            'single' => true,
            'list'   => [
                'columns' => [
                    'title',
                    'body' => 'wysiwyg',
                    'date' => [
                        'strategy' => 'date',
                        'options'  => [
                            'format' => 'd-m-Y H:i',
                        ],
                    ],
                    'test' => true,
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(4, $output->list->columns);

        /** @var ModelListColumnData $column */
        $column = $output->list->columns['title'];
        static::assertInstanceOf(ModelListColumnDataInterface::class, $column);
        static::assertEmpty($column->source);
        static::assertEmpty($column->strategy);
        static::assertEmpty($column->options);

        $column = $output->list->columns['body'];
        static::assertInstanceOf(ModelListColumnDataInterface::class, $column);
        static::assertEmpty($column->source);
        static::assertEquals('wysiwyg', $column->strategy);
        static::assertEmpty($column->options);

        $column = $output->list->columns['date'];
        static::assertInstanceOf(ModelListColumnDataInterface::class, $column);
        static::assertEmpty($column->source);
        static::assertEquals('date', $column->strategy);
        static::assertEquals(['format' => 'd-m-Y H:i'], $column->options);

        $column = $output->list->columns['test'];
        static::assertInstanceOf(ModelListColumnDataInterface::class, $column);
    }

    /**
     * @test
     */
    function it_disables_list_filters_for_boolean_false()
    {
        $input = [
            'list' => [
                'filters' => false,
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);
        static::assertTrue($output->list->disable_filters);
    }

    /**
     * @test
     */
    function it_normalizes_list_filter()
    {
        $input = [
            'single' => true,
            'list'   => [
                'filters' => [
                    'title',
                    'type' => 'dropdown',
                    'any'  => [
                        'label'    => 'Anything',
                        'target'   => '*',
                        'strategy' => 'string-split',
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(3, $output->list->filters);

        /** @var ModelListFilterData $filter */
        $filter = $output->list->filters['title'];
        static::assertInstanceOf(ModelFilterDataInterface::class, $filter);
        static::assertEmpty($filter->target);
        static::assertEmpty($filter->strategy);
        static::assertEmpty($filter->options);

        $filter = $output->list->filters['type'];
        static::assertInstanceOf(ModelFilterDataInterface::class, $filter);
        static::assertEmpty($filter->target);
        static::assertEquals('dropdown', $filter->strategy);
        static::assertEmpty($filter->options);

        $filter = $output->list->filters['any'];
        static::assertInstanceOf(ModelFilterDataInterface::class, $filter);
        static::assertEquals('*', $filter->target);
        static::assertEquals('string-split', $filter->strategy);
        static::assertEquals('Anything', $filter->label);
    }

    /**
     * @test
     */
    function it_disables_list_scopes_for_boolean_false()
    {
        $input = [
            'list' => [
                'scopes' => false,
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);
        static::assertTrue($output->list->disable_scopes);
    }

    /**
     * @test
     */
    function it_normalizes_list_scopes()
    {
        $input = [
            'list' => [
                'scopes' => [
                    'normal',
                    'used'    => 'test',
                    'deleted' => [
                        'label'    => 'Test',
                        'method'   => 'testMethod',
                        'strategy' => 'string',
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(3, $output->list->scopes);

        /** @var ModelScopeData $scope */
        $scope = $output->list->scopes['normal'];
        static::assertInstanceOf(ModelScopeDataInterface::class, $scope);
        static::assertEmpty($scope->strategy);
        static::assertEquals('normal', $scope->method);
        static::assertEmpty($scope->label);

        $scope = $output->list->scopes['used'];
        static::assertInstanceOf(ModelScopeDataInterface::class, $scope);
        static::assertEquals('test', $scope->strategy);

        $scope = $output->list->scopes['deleted'];
        static::assertInstanceOf(ModelScopeDataInterface::class, $scope);
        static::assertEquals('string', $scope->strategy);
        static::assertEquals('testMethod', $scope->method);
        static::assertEquals('Test', $scope->label);
    }

    /**
     * @test
     */
    function it_normalizes_list_parents()
    {
        $input = [
            'list' => [
                'parents' => [
                    'customKey' => 'relationA',
                    'relationB' => [
                        'relation' => 'relationB',
                        'field'    => 'string',
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(2, $output->list->parents);

        /** @var ModelListParentData $parent */
        $parent = $output->list->parents['customKey'];
        static::assertInstanceOf(ModelListParentDataInterface::class, $parent);
        static::assertEquals('relationA', $parent->relation);
        static::assertEmpty($parent->field);

        $parent = $output->list->parents['relationB'];
        static::assertInstanceOf(ModelListParentDataInterface::class, $parent);
        static::assertEquals('relationB', $parent->relation);
        static::assertEquals('string', $parent->field);
    }


    // ------------------------------------------------------------------------------
    //      Form
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_normalizes_form_fields()
    {
        $input = [
            'form' => [
                'fields' => [
                    'title',
                    'body' => 'wysiwyg',
                    'date' => [
                        'display_strategy' => 'datetime',
                        'store_strategy'   => 'date',
                        'options'          => [
                            'format' => 'd-m-Y H:i',
                        ],
                    ],
                    'help_field_a' => [
                        'display_strategy' => 'test',
                        'store_strategy'   => 'test',
                        'help' => 'help string',
                    ],
                    'help_field_b' => [
                        'display_strategy' => 'test',
                        'store_strategy'   => 'test',
                        'help' => [
                            'label_tooltip' => 'help string',
                        ],
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(5, $output->form->fields);

        /** @var ModelFormFieldData $field */
        $field = $output->form->fields['title'];
        static::assertInstanceOf(ModelFormFieldDataInterface::class, $field);
        static::assertEmpty($field->source);
        static::assertEmpty($field->display_strategy);
        static::assertEmpty($field->store_strategy);
        static::assertEmpty($field->options);

        $field = $output->form->fields['body'];
        static::assertInstanceOf(ModelFormFieldDataInterface::class, $field);
        static::assertEmpty($field->source);
        static::assertEquals('wysiwyg', $field->display_strategy);
        static::assertEmpty($field->store_strategy);
        static::assertEmpty($field->options);

        $field = $output->form->fields['date'];
        static::assertInstanceOf(ModelFormFieldDataInterface::class, $field);
        static::assertEmpty($field->source);
        static::assertEquals('datetime', $field->display_strategy);
        static::assertEquals('date', $field->store_strategy);
        static::assertEquals(['format' => 'd-m-Y H:i'], $field->options);

        $field = $output->form->fields['help_field_a'];
        static::assertInstanceOf(ModelFormFieldDataInterface::class, $field);
        static::assertInstanceOf(ModelFormFieldHelpData::class, $field->help);
        static::assertEquals('help string', $field->help->field->text);

        $field = $output->form->fields['help_field_b'];
        static::assertInstanceOf(ModelFormFieldDataInterface::class, $field);
        static::assertInstanceOf(ModelFormFieldHelpData::class, $field->help);
        static::assertEquals('help string', $field->help->label_tooltip->text);
    }

    // ------------------------------------------------------------------------------
    //      Show
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_normalizes_show_columns()
    {
        $input = [
            'show' => [
                'fields' => [
                    'title',
                    'body' => 'wysiwyg',
                    'date' => [
                        'strategy' => 'date',
                        'options'  => [
                            'format' => 'd-m-Y H:i',
                        ],
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(3, $output->show->fields);

        /** @var ModelShowFieldData $field */
        $field = $output->show->fields['title'];
        static::assertInstanceOf(ModelShowFieldDataInterface::class, $field);
        static::assertEmpty($field->source);
        static::assertEmpty($field->strategy);
        static::assertEmpty($field->options);

        $field = $output->show->fields['body'];
        static::assertInstanceOf(ModelShowFieldDataInterface::class, $field);
        static::assertEmpty($field->source);
        static::assertEquals('wysiwyg', $field->strategy);
        static::assertEmpty($field->options);

        $field = $output->show->fields['date'];
        static::assertInstanceOf(ModelShowFieldDataInterface::class, $field);
        static::assertEmpty($field->source);
        static::assertEquals('date', $field->strategy);
        static::assertEquals(['format' => 'd-m-Y H:i'], $field->options);
    }

    // ------------------------------------------------------------------------------
    //      Export
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_normalizes_default_export_columns()
    {
        $input = [
            'export' => [
                'columns' => [
                    'title',
                    'body' => 'text',
                    'date' => [
                        'strategy' => 'datetime',
                        'options'  => [
                            'format' => 'd-m-Y H:i',
                        ],
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(3, $output->export->columns);

        /** @var ModelExportColumnData $column */
        $column = $output->export->columns['title'];
        static::assertInstanceOf(ModelExportColumnDataInterface::class, $column);
        static::assertEmpty($column->source);
        static::assertEmpty($column->strategy);
        static::assertEmpty($column->options);

        $column = $output->export->columns['body'];
        static::assertInstanceOf(ModelExportColumnDataInterface::class, $column);
        static::assertEmpty($column->source);
        static::assertEquals('text', $column->strategy);
        static::assertEmpty($column->options);

        $column = $output->export->columns['date'];
        static::assertInstanceOf(ModelExportColumnDataInterface::class, $column);
        static::assertEmpty($column->source);
        static::assertEquals('datetime', $column->strategy);
        static::assertEquals(['format' => 'd-m-Y H:i'], $column->options);
    }

    /**
     * @test
     */
    function it_normalizes_export_strategies()
    {
        $input = [
            'export' => [
                'strategies' => [
                    'csv' => true,
                    'xml' => [
                        'strategy' => 'excel',
                        'permissions' => 'test.permission',
                        'columns'     => [
                            'title',
                            'body' => 'text',
                        ],
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        $output = $interpreter->interpret($input);

        static::assertInstanceOf(ModelInformation::class, $output);

        static::assertCount(2, $output->export->strategies);

        /** @var ModelExportStrategyData $strategy */
        $strategy = $output->export->strategies['csv'];
        static::assertInstanceOf(ModelExportStrategyDataInterface::class, $strategy);
        static::assertEquals('csv', $strategy->strategy);

        $strategy = $output->export->strategies['xml'];
        static::assertInstanceOf(ModelExportStrategyDataInterface::class, $strategy);
        static::assertEquals('excel', $strategy->strategy);

        static::assertCount(2, $strategy->columns);

        /** @var ModelExportColumnData $column */
        $column = $strategy->columns['title'];
        static::assertInstanceOf(ModelExportColumnDataInterface::class, $column);
        static::assertEmpty($column->strategy);

        $column = $strategy->columns['body'];
        static::assertInstanceOf(ModelExportColumnDataInterface::class, $column);
        static::assertEquals('text', $column->strategy);
    }

    // ------------------------------------------------------------------------------
    //      Special
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_rethrows_model_configuration_exceptions_with_relevant_context_added()
    {
        $input = [
            'list' => [
                'columns' => [
                    'test' => [
                        'does-not-exist' => true,
                    ],
                ],
            ],
        ];

        $interpreter = new CmsModelInformationInterpreter;

        try {
            $interpreter->interpret($input);

            static::fail('Should have thrown exception');

        } catch (ModelConfigurationDataException $e) {

            static::assertEquals('list.columns.test.does-not-exist', $e->getDotKey());
        }
    }

}
