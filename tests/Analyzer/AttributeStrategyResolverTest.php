<?php
namespace Czim\CmsModels\Test\Analyzer;

use Czim\CmsModels\Analyzer\AttributeStrategyResolver;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\ExportColumnStrategy;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\FormStoreStrategy;
use Czim\CmsModels\Support\Enums\ListDisplayStrategy;
use Czim\CmsModels\Test\TestCase;

class AttributeStrategyResolverTest extends TestCase
{

    // ------------------------------------------------------------------------------
    //      List
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_list_strategy_for_boolean()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::BOOLEAN,
            'nullable' => true,
        ]);
        static::assertEquals(ListDisplayStrategy::CHECK_NULLABLE, $resolver->determineListDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::BOOLEAN,
            'nullable' => false,
        ]);
        static::assertEquals(ListDisplayStrategy::CHECK, $resolver->determineListDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_list_strategy_for_date()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'date',
        ]);
        static::assertEquals(ListDisplayStrategy::DATE, $resolver->determineListDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'time',
        ]);
        static::assertEquals(ListDisplayStrategy::TIME, $resolver->determineListDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'datetime',
        ]);
        static::assertEquals(ListDisplayStrategy::DATETIME, $resolver->determineListDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'timestamp',
        ]);
        static::assertEquals(ListDisplayStrategy::DATETIME, $resolver->determineListDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_list_strategy_for_stapler_attachment()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STAPLER_ATTACHMENT,
            'type' => 'image',
        ]);
        static::assertEquals(ListDisplayStrategy::STAPLER_THUMBNAIL, $resolver->determineListDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STAPLER_ATTACHMENT,
            'type' => 'file',
        ]);
        static::assertEquals(ListDisplayStrategy::STAPLER_FILENAME, $resolver->determineListDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_falls_back_to_null_for_list_strategy()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => 'unknown',
        ]);
        static::assertNull($resolver->determineListDisplayStrategy($data));
    }

    // ------------------------------------------------------------------------------
    //      Form Display
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_boolean()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::BOOLEAN,
            'nullable' => true,
        ]);
        static::assertEquals(FormDisplayStrategy::BOOLEAN_DROPDOWN, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::BOOLEAN,
            'nullable' => false,
        ]);
        static::assertEquals(FormDisplayStrategy::BOOLEAN_CHECKBOX, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_integer()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::INTEGER,
        ]);
        static::assertEquals(FormDisplayStrategy::NUMERIC_INTEGER, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_float()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::FLOAT,
        ]);
        static::assertEquals(FormDisplayStrategy::NUMERIC_DECIMAL, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_string()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STRING,
            'type' => 'enum',
        ]);
        static::assertEquals(FormDisplayStrategy::DROPDOWN, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STRING,
            'type' => 'year',
        ]);
        static::assertEquals(FormDisplayStrategy::NUMERIC_YEAR, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STRING,
            'type' => 'varchar',
        ]);
        static::assertEquals(FormDisplayStrategy::TEXT, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STRING,
            'type' => 'text',
        ]);
        static::assertEquals(FormDisplayStrategy::WYSIWYG, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STRING,
            'type' => 'blob',
        ]);
        static::assertEquals(FormDisplayStrategy::TEXTAREA, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STRING,
            'type' => 'varbinary',
        ]);
        static::assertEquals(FormDisplayStrategy::TEXTAREA, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_date()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'date',
        ]);
        static::assertEquals(FormDisplayStrategy::DATEPICKER_DATE, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'time',
        ]);
        static::assertEquals(FormDisplayStrategy::DATEPICKER_TIME, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'datetime',
        ]);
        static::assertEquals(FormDisplayStrategy::DATEPICKER_DATETIME, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'timestamp',
        ]);
        static::assertEquals(FormDisplayStrategy::DATEPICKER_DATETIME, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_array_or_json()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::ARRAY_CAST,
        ]);
        static::assertEquals(FormDisplayStrategy::TEXTAREA, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::JSON,
        ]);
        static::assertEquals(FormDisplayStrategy::TEXTAREA, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_stapler_attachment()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STAPLER_ATTACHMENT,
            'type' => 'image',
        ]);
        static::assertEquals(FormDisplayStrategy::ATTACHMENT_STAPLER_IMAGE, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STAPLER_ATTACHMENT,
            'type' => 'file',
        ]);
        static::assertEquals(FormDisplayStrategy::ATTACHMENT_STAPLER_FILE, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_falls_back_to_null_for_form_display_strategy()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => 'unknown',
        ]);
        static::assertNull($resolver->determineFormDisplayStrategy($data));
    }

    // ------------------------------------------------------------------------------
    //      Form Store
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_form_store_strategy_for_boolean()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'       => AttributeCast::BOOLEAN,
            'nullable'   => false,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::BOOLEAN, $resolver->determineFormStoreStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_store_strategy_for_date()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'       => AttributeCast::DATE,
            'nullable'   => false,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::DATE, $resolver->determineFormStoreStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_store_strategy_for_stapler_attachment()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'       => AttributeCast::STAPLER_ATTACHMENT,
            'type'       => 'image',
            'nullable'   => false,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::STAPLER, $resolver->determineFormStoreStrategy($data));
    }

    /**
     * @test
     */
    function it_appends_parameters_to_form_store_strategy_for_nullable_and_translated()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'       => 'unknown',
            'nullable'   => true,
            'translated' => true,
        ]);
        static::assertEquals(':translated,nullable', $resolver->determineFormStoreStrategy($data));

        $data = new ModelAttributeData([
            'cast'       => AttributeCast::BOOLEAN,
            'nullable'   => true,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::BOOLEAN . ':nullable', $resolver->determineFormStoreStrategy($data));

        $data = new ModelAttributeData([
            'cast'       => AttributeCast::BOOLEAN,
            'nullable'   => false,
            'translated' => true,
        ]);
        static::assertEquals(FormStoreStrategy::BOOLEAN . ':translated', $resolver->determineFormStoreStrategy($data));
    }

    /**
     * @test
     */
    function it_falls_back_to_null_for_form_store_strategy()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'     => 'unknown',
            'nullable' => false,
        ]);
        static::assertNull($resolver->determineFormStoreStrategy($data));
    }

    // ------------------------------------------------------------------------------
    //      Form Store Options
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_form_store_options_for_boolean()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::BOOLEAN,
        ]);
        static::assertEquals(
            ['type' => 'number', 'min' => 0, 'max' => 1, 'size' => 1],
            $resolver->determineFormStoreOptions($data)
        );
    }

    /**
     * @test
     */
    function it_determines_form_store_options_for_integer()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::INTEGER,
            'type'     => 'tinyint',
            'unsigned' => true,
            'length'   => 4,
        ]);
        static::assertEquals(
            ['type' => 'number', 'min' => 0, 'max' => 255, 'size' => 3],
            $resolver->determineFormStoreOptions($data)
        );

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::INTEGER,
            'type'     => 'mediumint',
            'unsigned' => false,
        ]);
        static::assertEquals(
            ['type' => 'number', 'min' => -32769, 'max' => 32767, 'size' => 5],
            $resolver->determineFormStoreOptions($data)
        );

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::INTEGER,
            'type'     => 'int',
            'unsigned' => true,
        ]);
        static::assertEquals(
            ['type' => 'number', 'min' => 0, 'max' => 4294967295, 'size' => 10],
            $resolver->determineFormStoreOptions($data)
        );

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::INTEGER,
            'type'     => 'bigint',
            'unsigned' => true,
        ]);
        static::assertEquals(
            ['type' => 'number', 'min' => 0, 'max' => pow(256, 8) - 1, 'size' => 18],
            $resolver->determineFormStoreOptions($data)
        );
    }

    /**
     * @test
     */
    function it_determines_form_store_options_for_float()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast'   => AttributeCast::STRING,
            'type'   => 'varchar',
            'length' => 255,
        ]);
        static::assertEquals(
            ['maxlength' => 255],
            $resolver->determineFormStoreOptions($data)
        );

        $data = new ModelAttributeData([
            'cast'   => AttributeCast::STRING,
            'type'   => 'enum',
            'length' => null,
            'values' => ['a', 'b'],
        ]);
        static::assertEquals(
            ['maxlength' => null, 'values' => ['a', 'b']],
            $resolver->determineFormStoreOptions($data)
        );
    }

    /**
     * @test
     */
    function it_determines_form_store_options_for_string()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::FLOAT,
        ]);
        static::assertEquals(
            ['type' => 'number', 'step' => 0.01],
            $resolver->determineFormStoreOptions($data)
        );
    }

    /**
     * @test
     */
    function it_falls_back_to_empty_array_for_form_store_options()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => 'unknown',
        ]);
        static::assertEquals([], $resolver->determineFormStoreOptions($data));
    }

    // ------------------------------------------------------------------------------
    //      Export Column
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_export_column_strategy_for_boolean()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::BOOLEAN,
        ]);
        static::assertEquals(ExportColumnStrategy::BOOLEAN_STRING, $resolver->determineExportColumnStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_export_column_strategy_for_date()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'date',
        ]);
        static::assertEquals(ExportColumnStrategy::DATE, $resolver->determineExportColumnStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'time',
        ]);
        static::assertEquals(ExportColumnStrategy::DATE, $resolver->determineExportColumnStrategy($data));

        $data = new ModelAttributeData([
            'cast' => AttributeCast::DATE,
            'type' => 'datetime',
        ]);
        static::assertEquals(ExportColumnStrategy::DATE, $resolver->determineExportColumnStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_export_column_strategy_for_stapler_attachment()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => AttributeCast::STAPLER_ATTACHMENT,
            'type' => 'image',
        ]);
        static::assertEquals(ExportColumnStrategy::STAPLER_FILE_LINK, $resolver->determineExportColumnStrategy($data));
    }

    /**
     * @test
     */
    function it_falls_back_to_null_for_export_column_strategy()
    {
        $resolver = new AttributeStrategyResolver;

        $data = new ModelAttributeData([
            'cast' => 'unknown',
        ]);
        static::assertNull($resolver->determineExportColumnStrategy($data));
    }

}
