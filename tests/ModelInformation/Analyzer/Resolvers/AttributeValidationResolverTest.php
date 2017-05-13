<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Resolvers;

use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeValidationResolver;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Test\TestCase;

/**
 * Class AttributeValidationResolverTest
 *
 * Note that 'empty' rules will contain Laravel 5.4's explicit 'nullable'.
 *
 * @group analysis
 */
class AttributeValidationResolverTest extends TestCase
{

    /**
     * @test
     */
    function it_determines_validation_rules_for_boolean()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::BOOLEAN,
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        static::assertEquals(
            ['nullable'],
            $resolver->determineValidationRules($data, $field)
        );
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_integer()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::INTEGER,
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('integer', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_float()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::FLOAT,
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('numeric', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_enum()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::STRING,
            'type'     => 'enum',
            'values'   => ['a','b','c'],
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('in:a,b,c', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_year()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::STRING,
            'type'     => 'year',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('digits:4', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_varchar_and_tinytext()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::STRING,
            'type'     => 'varchar',
            'length'   => 255,
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('string', $rules));
        static::assertTrue(in_array('max:255', $rules));
        static::assertTrue(in_array('required', $rules));


        $data = new ModelAttributeData([
            'cast'     => AttributeCast::STRING,
            'type'     => 'tinytext',
            'length'   => 255,
            'nullable' => true,
        ]);

        $field = new ModelFormFieldData([
            'required' => false,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('string', $rules));
        static::assertTrue(in_array('max:255', $rules));
        static::assertFalse(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_char()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::STRING,
            'type'     => 'char',
            'length'   => 18,
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('string', $rules));
        static::assertTrue(in_array('max:18', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_text_and_blob()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::STRING,
            'type'     => 'text',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('string', $rules));
        static::assertTrue(in_array('required', $rules));

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::STRING,
            'type'     => 'varbinary',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('string', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_date()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::DATE,
            'type'     => 'date',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('date', $rules));
        static::assertTrue(in_array('required', $rules));

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::DATE,
            'type'     => 'datetime',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('date', $rules));
        static::assertTrue(in_array('required', $rules));

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::DATE,
            'type'     => 'timestamp',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('date', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_time()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::DATE,
            'type'     => 'time',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('regex:#^\d{1,2}:\d{1,2}(:\d{1,2})?$#', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_determines_validation_rules_for_json()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => AttributeCast::JSON,
            'type'     => 'json',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        $rules = $resolver->determineValidationRules($data, $field);
        static::assertTrue(in_array('json', $rules));
        static::assertTrue(in_array('required', $rules));
    }

    /**
     * @test
     */
    function it_falls_back_to_empty_rules_for_unknown_data()
    {
        $resolver = new AttributeValidationResolver;

        $data = new ModelAttributeData([
            'cast'     => 'unknown',
            'nullable' => false,
        ]);

        $field = new ModelFormFieldData([
            'required' => false,
        ]);

        static::assertEquals(
            ['nullable'],
            $resolver->determineValidationRules($data, $field)
        );
    }

}
