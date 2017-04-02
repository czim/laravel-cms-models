<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichValidationData;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleAssocArrayFormatValidation;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleBrokenValidation;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleNoValidation;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleNumericValidation;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleStringFormatValidation;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleStringValidation;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class EnrichValidationDataTest
 *
 * @group enrichment
 */
class EnrichValidationDataTest extends TestCase
{

    /**
     * @test
     */
    function it_skips_enrichment_without_problems_if_no_form_fields_are_set()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->form->fields = [];

        $step->enrich($info, []);

        static::assertEmpty($info->form->validation->create);
        static::assertEmpty($info->form->validation->update);
    }

    /**
     * @test
     */
    function it_enriches_create_and_update_rules_based_on_shared_default_rules()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'source'           => 'name',
                'display_strategy' => 'display1',
            ]),
            'number' => new ModelFormfieldData([
                'source'           => 'number',
                'display_strategy' => 'display2',
            ]),
        ];

        $info->form->validation->shared = [
            'shared_rule'       => 'required',
            'overridden_create' => 'integer|max:10',
            'overridden_update' => 'integer|max:20',
        ];
        $info->form->validation->create = [
            'overridden_create' => 'string',
        ];
        $info->form->validation->update = [
            'overridden_update' => 'string',
        ];

        $step->enrich($info, []);

        $rules = $info->form->validation->create;

        static::assertCount(3, $rules);
        static::assertArrayHasKey('shared_rule', $rules);
        static::assertArrayHasKey('overridden_create', $rules);
        static::assertArrayHasKey('overridden_update', $rules);

        static::assertEquals(['required'], $rules['shared_rule']);
        static::assertEquals(['string'], $rules['overridden_create']);
        static::assertEquals(['integer', 'max:20'], $rules['overridden_update']);

        $rules = $info->form->validation->update;

        static::assertCount(3, $rules);
        static::assertArrayHasKey('shared_rule', $rules);
        static::assertArrayHasKey('overridden_create', $rules);
        static::assertArrayHasKey('overridden_update', $rules);

        static::assertEquals(['required'], $rules['shared_rule']);
        static::assertEquals(['integer', 'max:10'], $rules['overridden_create']);
        static::assertEquals(['string'], $rules['overridden_update']);
    }
    
    /**
     * @test
     */
    function it_ignores_shared_rules_if_specific_create_or_update_rules_are_marked_boolean_false()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'source'           => 'name',
                'display_strategy' => 'display1',
            ]),
            'number' => new ModelFormfieldData([
                'source'           => 'number',
                'display_strategy' => 'display2',
            ]),
        ];

        $info->form->validation->shared = [
            'shared_rule' => 'required',
        ];
        $info->form->validation->create = false;
        $info->form->validation->update = false;

        $step->enrich($info, []);

        static::assertEmpty($info->form->validation->create);
        static::assertEmpty($info->form->validation->update);
    }

    /**
     * @test
     */
    function it_omits_specific_shared_rules_by_key_if_specific_create_or_update_rule_keys_are_marked_boolean_false()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'source'           => 'name',
                'display_strategy' => 'display1',
            ]),
            'number' => new ModelFormfieldData([
                'source'           => 'number',
                'display_strategy' => 'display2',
            ]),
        ];

        $info->form->validation->shared = [
            'shared_rule'  => 'required',
            'another_rule' => 'string',
        ];
        $info->form->validation->create = [
            'another_rule' => false,
        ];
        $info->form->validation->update = [
            'shared_rule' => false,
        ];

        $step->enrich($info, []);

        static::assertArrayNotHasKey('another_rule', $info->form->validation->create);
        static::assertArrayNotHasKey('shared_rule', $info->form->validation->update);
    }

    /**
     * @test
     */
    function it_ignores_shared_rules_for_specific_create_or_update_rules_in_replace_mode()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'source'           => 'name',
                'display_strategy' => 'display1',
            ]),
            'number' => new ModelFormfieldData([
                'source'           => 'number',
                'display_strategy' => 'display2',
            ]),
        ];

        $info->form->validation->shared = [
            'shared_rule' => 'required',
        ];
        $info->form->validation->create = [
            'replacement' => 'string',
        ];
        $info->form->validation->update = [];
        $info->form->validation->create_replace = true;
        $info->form->validation->update_replace = true;

        $step->enrich($info, []);

        static::assertArrayHasKey('replacement', $info->form->validation->create);
        static::assertArrayNotHasKey('another_rule', $info->form->validation->create);
        static::assertArrayNotHasKey('shared_rule', $info->form->validation->update);
    }
    
    /**
     * @test
     */
    function it_can_include_specific_rules_in_replace_mode_if_the_key_is_listed_as_a_value_in_the_create_or_update_section()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'source'           => 'name',
                'display_strategy' => 'display1',
            ]),
            'number' => new ModelFormfieldData([
                'source'           => 'number',
                'display_strategy' => 'display2',
            ]),
        ];

        $info->form->validation->shared = [
            'shared_rule'   => 'required',
            'not_in_create' => 'string',
        ];
        $info->form->validation->create = [
            'replacement' => 'string',
            'shared_rule',
        ];
        $info->form->validation->create_replace = true;

        $step->enrich($info, []);

        static::assertArrayHasKey('shared_rule', $info->form->validation->create);
        static::assertArrayHasKey('replacement', $info->form->validation->create);
        static::assertArrayNotHasKey('not_in_create', $info->form->validation->create);
        static::assertEquals(['required'], $info->form->validation->create['shared_rule']);

        static::assertArrayHasKey('shared_rule', $info->form->validation->update);
        static::assertArrayHasKey('not_in_create', $info->form->validation->update);
    }

    // ------------------------------------------------------------------------------
    //      Form Strategy Based Rules
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_includes_form_field_generated_rules()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
            'extra'  => new ModelAttributeData(['name' => 'extra']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'key'            => 'name',
                'source'         => 'name',
                'store_strategy' => TestSimpleStringValidation::class,
            ]),
            'number' => new ModelFormfieldData([
                'key'            => 'number',
                'source'         => 'number',
                'store_strategy' => TestSimpleNumericValidation::class,
            ]),
            'extra' => new ModelFormfieldData([
                'key'            => 'extra',
                'source'         => 'extra',
                'store_strategy' => TestSimpleNoValidation::class,
            ]),
            'string_format' => new ModelFormfieldData([
                'key'            => 'string_format',
                'source'         => 'string_format',
                'store_strategy' => TestSimpleStringFormatValidation::class,
            ]),
            'assoc_format' => new ModelFormfieldData([
                'key'            => 'assoc_format',
                'source'         => 'assoc_format',
                'store_strategy' => TestSimpleAssocArrayFormatValidation::class,
            ]),
        ];

        $step->enrich($info, []);

        $rules = $info->form->validation->create;
        static::assertCount(5, $rules);
        static::assertArrayHasKey('name', $rules);
        static::assertArrayHasKey('number', $rules);
        static::assertEquals(['string', 'size:10'], $rules['name']);
        static::assertEquals(['integer', 'max:99'], $rules['number']);
        static::assertEquals(['string|size:10'], $rules['string_format']);
        static::assertEquals(['required', 'string'], $rules['field_a']);
        static::assertEquals('size:10', $rules['field_b']);

        $rules = $info->form->validation->update;
        static::assertCount(6, $rules);
        static::assertArrayHasKey('name', $rules);
        static::assertArrayHasKey('number', $rules);
        static::assertArrayHasKey('extra', $rules);
        static::assertEquals(['string', 'size:20'], $rules['name']);
        static::assertEquals(['integer', 'max:250'], $rules['number']);
        static::assertEquals(['required'], $rules['extra']);
        static::assertEquals(['string|size:20'], $rules['string_format']);
        static::assertEquals(['required', 'string'], $rules['field_a']);
        static::assertEquals('size:20', $rules['field_b']);
    }

    /**
     * @test
     */
    function it_enriches_specific_rules_with_form_field_generated_rules()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
            'extra'  => new ModelAttributeData(['name' => 'extra']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'key'            => 'name',
                'source'         => 'name',
                'store_strategy' => TestSimpleStringValidation::class,
            ]),
            'number' => new ModelFormfieldData([
                'key'            => 'number',
                'source'         => 'number',
                'store_strategy' => TestSimpleNumericValidation::class,
            ]),
            'extra' => new ModelFormfieldData([
                'key'            => 'extra',
                'source'         => 'extra',
                'store_strategy' => TestSimpleNoValidation::class,
            ]),
        ];

        $info->form->validation->shared = [
            'extra' => 'date',
        ];
        $info->form->validation->create = [
            'number' => 'max:1',
        ];
        $info->form->validation->update = [
            'number' => 'max:2',
        ];

        $step->enrich($info, []);

        $rules = $info->form->validation->create;
        static::assertCount(3, $rules);
        static::assertArrayHasKey('name', $rules);
        static::assertArrayHasKey('number', $rules);
        static::assertArrayHasKey('extra', $rules);
        static::assertEquals(['string', 'size:10'], $rules['name']);
        static::assertEquals(['max:1'], $rules['number']);
        static::assertEquals(['date'], $rules['extra']);

        $rules = $info->form->validation->update;
        static::assertCount(3, $rules);
        static::assertArrayHasKey('name', $rules);
        static::assertArrayHasKey('number', $rules);
        static::assertArrayHasKey('extra', $rules);
        static::assertEquals(['string', 'size:20'], $rules['name']);
        static::assertEquals(['max:2'], $rules['number']);
        static::assertEquals(['date'], $rules['extra']);
    }

    /**
     * @test
     */
    function it_only_includes_validation_rules_for_fields_explicitly_indicated_in_replace_mode()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
            'extra'  => new ModelAttributeData(['name' => 'extra']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'key'            => 'name',
                'source'         => 'name',
                'store_strategy' => TestSimpleStringValidation::class,
            ]),
            'number' => new ModelFormfieldData([
                'key'            => 'number',
                'source'         => 'number',
                'store_strategy' => TestSimpleNumericValidation::class,
            ]),
            'extra' => new ModelFormfieldData([
                'key'            => 'extra',
                'source'         => 'extra',
                'store_strategy' => TestSimpleNoValidation::class,
            ]),
        ];

        $info->form->validation->create = [
            'name',
            'number' => 'max:1',
            'extra', // should still not be present due to store not offering rules
        ];
        $info->form->validation->update = [
            'number' => 'max:2',
            'extra',
        ];
        $info->form->validation->create_replace = true;
        $info->form->validation->update_replace = true;

        $step->enrich($info, []);

        $rules = $info->form->validation->create;
        static::assertCount(2, $rules);
        static::assertArrayHasKey('name', $rules);
        static::assertArrayHasKey('number', $rules);
        static::assertEquals(['string', 'size:10'], $rules['name']);
        static::assertEquals(['max:1'], $rules['number']);

        $rules = $info->form->validation->update;
        static::assertCount(2, $rules);
        static::assertArrayHasKey('number', $rules);
        static::assertArrayHasKey('extra', $rules);
        static::assertEquals(['max:2'], $rules['number']);
        static::assertEquals(['required'], $rules['extra']);
    }

    /**
     * @test
     */
    function it_does_not_enrich_specific_validation_rules_with_form_field_generated_rule_if_explicitly_disabled()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name' => new ModelAttributeData(['name' => 'name']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'key'            => 'name',
                'source'         => 'name',
                'store_strategy' => TestSimpleStringValidation::class,
            ]),
        ];

        $info->form->validation->create = [
            'name' => false,
        ];

        $step->enrich($info, []);

        static::assertEmpty($info->form->validation->create);
    }

    /**
     * @test
     */
    function it_does_not_include_validation_rules_for_fields_that_are_not_relevant_for_create_or_update()
    {
        // form field generated rule for field that is not in create/update
        // form field generated rule for field that is not in layout

        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name'   => new ModelAttributeData(['name' => 'name']),
            'number' => new ModelAttributeData(['name' => 'title']),
            'extra'  => new ModelAttributeData(['name' => 'extra']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'key'            => 'name',
                'source'         => 'name',
                'store_strategy' => TestSimpleStringValidation::class,
                'create'         => false,
                'update'         => true,
            ]),
            'number' => new ModelFormfieldData([
                'key'            => 'number',
                'source'         => 'number',
                'store_strategy' => TestSimpleNumericValidation::class,
                'create'         => true,
                'update'         => false,
            ]),
            'extra' => new ModelFormfieldData([
                'key'            => 'extra',
                'source'         => 'extra',
                'store_strategy' => TestSimpleNoValidation::class,
            ]),
        ];

        $info->form->layout = [
            'number',
            'name',
        ];

        $step->enrich($info, []);

        $rules = $info->form->validation->create;
        static::assertCount(1, $rules);
        static::assertArrayHasKey('number', $rules);
        static::assertEquals(['integer', 'max:99'], $rules['number']);

        $rules = $info->form->validation->update;
        static::assertCount(1, $rules);
        static::assertArrayHasKey('name', $rules);
        static::assertEquals(['string', 'size:20'], $rules['name']);
    }

    /**
     * @test
     */
    function it_throws_a_contextually_decorated_exception_if_something_goes_wrong_while_retrieving_form_field_validation_rules()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichValidationData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'name' => new ModelAttributeData(['name' => 'name']),
        ];

        $info->form->fields = [
            'name' => new ModelFormfieldData([
                'key'            => 'name',
                'source'         => 'name',
                'store_strategy' => TestSimpleBrokenValidation::class,
            ]),
        ];

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('form.validation.create', $e->getSection());
            static::assertEquals('name', $e->getKey());
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
