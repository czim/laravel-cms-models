<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeStrategyResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichFormFieldData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestNotIncrementing;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class EnrichFormFieldDataTest
 *
 * @group enrichment
 */
class EnrichFormFieldDataTest extends TestCase
{

    /**
     * @test
     */
    function it_fills_form_fields_based_on_attributes_and_relations_if_none_set()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display1', 'display2');
        $mockAttributeResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store1', 'store2');
        $mockAttributeResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);
        $mockRelationResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display3', 'display4');
        $mockRelationResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store3', 'store4');
        $mockRelationResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        // Hidden attributes and foreign key columns should not be included
        $info->attributes = [
            'title'   => new ModelAttributeData(['name' => 'title']),
            'name'    => new ModelAttributeData(['name' => 'name', 'hidden' => true]),
            'active'  => new ModelAttributeData(['name' => 'active']),
        ];
        $info->relations  = [
            'single' => new ModelRelationData([
                'name'         => 'single',
                'method'       => 'single',
                'type'         => RelationType::BELONGS_TO,
                'foreign_keys' => [
                    'test_id',
                ],
            ]),
            'many' => new ModelRelationData([
                'name'   => 'many',
                'method' => 'many',
                'type'   => RelationType::HAS_MANY,
            ]),
        ];

        $info->form->fields = [];

        $step = new EnrichFormFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);


        static::assertEquals(['title', 'active', 'single', 'many'], array_keys($info->form->fields));

        /** @var ModelFormFieldData $field */
        $field = $info->form->fields['title'];
        static::assertInstanceOf(ModelFormfieldData::class, $field);
        static::assertEquals('title', $field->key);
        static::assertEquals('display1', $field->display_strategy);
        static::assertEquals('store1', $field->store_strategy);

        $field = $info->form->fields['active'];
        static::assertInstanceOf(ModelFormfieldData::class, $field);
        static::assertEquals('active', $field->key);
        static::assertEquals('display2', $field->display_strategy);
        static::assertEquals('store2', $field->store_strategy);

        $field = $info->form->fields['single'];
        static::assertInstanceOf(ModelFormfieldData::class, $field);
        static::assertEquals('single', $field->key);
        static::assertEquals('display3', $field->display_strategy);
        static::assertEquals('store3', $field->store_strategy);

        $field = $info->form->fields['many'];
        static::assertInstanceOf(ModelFormfieldData::class, $field);
        static::assertEquals('many', $field->key);
        static::assertEquals('display4', $field->display_strategy);
        static::assertEquals('store4', $field->store_strategy);
    }

    /**
     * @test
     */
    function it_enriches_set_form_fields_based_on_attributes_and_relations()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display1', 'display2');
        $mockAttributeResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store1', 'store2');
        $mockAttributeResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);
        $mockRelationResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display3');
        $mockRelationResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store3');
        $mockRelationResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);

        $info = new ModelInformation;

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->attributes      = [
            'title'  => new ModelAttributeData(['name' => 'title']),
            'name'   => new ModelAttributeData(['name' => 'name']),
            'active' => new ModelAttributeData(['name' => 'active']),
        ];
        $info->relations = [
            'single' => new ModelRelationData([
                'name'         => 'single',
                'method'       => 'single',
                'type'         => RelationType::BELONGS_TO,
                'foreign_keys' => [
                    'test_id',
                ],
            ]),
            'many' => new ModelRelationData([
                'name'   => 'many',
                'method' => 'many',
                'type'   => RelationType::HAS_MANY,
            ]),
        ];

        $info->form->fields = [
            'name'   => new ModelFormfieldData(),
            'active' => new ModelFormfieldData(['display_strategy' => 'alt', 'source' => 'testing']),
            'new'    => new ModelFormfieldData(['source' => 'custom_column']),
            'many'   => new ModelFormfieldData(),
        ];

        $step = new EnrichFormFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);


        static::assertEquals(['name', 'active', 'new', 'many'], array_keys($info->form->fields));

        /** @var ModelFormFieldData $field */
        $field = $info->form->fields['name'];
        static::assertInstanceOf(ModelFormFieldData::class, $field);
        static::assertEquals('name', $field->key);
        static::assertEquals('display1', $field->display_strategy);
        static::assertEquals('store1', $field->store_strategy);

        $field = $info->form->fields['active'];
        static::assertInstanceOf(ModelFormFieldData::class, $field);
        static::assertEquals('active', $field->key);
        static::assertEquals('testing', $field->source);
        static::assertEquals('alt', $field->display_strategy);
        static::assertEquals('store2', $field->store_strategy);

        $field = $info->form->fields['new'];
        static::assertInstanceOf(ModelFormFieldData::class, $field);
        static::assertEquals('new', $field->key);
        static::assertEquals('custom_column', $field->source);

        $field = $info->form->fields['many'];
        static::assertInstanceOf(ModelFormfieldData::class, $field);
        static::assertEquals('many', $field->key);
        static::assertEquals('display3', $field->display_strategy);
        static::assertEquals('store3', $field->store_strategy);
    }

    /**
     * @test
     */
    function it_excludes_attributes_that_should_not_be_editable_by_default()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display1', 'display2');
        $mockAttributeResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store1', 'store2');
        $mockAttributeResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);
        $mockRelationResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display3', 'display4');
        $mockRelationResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store3', 'store4');
        $mockRelationResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->list->activatable   = true;
        $info->list->active_column = 'active';
        $info->list->orderable     = true;
        $info->list->order_column  = 'position';
        $info->timestamps          = true;
        $info->timestamp_created   = 'created_at';
        $info->timestamp_updated   = 'updated_at';

        $info->attributes = [
            // Include a normal attribute (main stapler field)
            'image'      => new ModelAttributeData(['name' => 'image', 'cast' => AttributeCast::STAPLER_ATTACHMENT]),
            // Exclude the incrementing key
            'id'         => new ModelAttributeData(['name' => 'id']),
            // Exclude hidden attributes
            'name'       => new ModelAttributeData(['name' => 'name', 'hidden' => true]),
            // Exclude foreign keys
            'test_id'    => new ModelAttributeData(['name' => 'test_id']),
            // Exclude activatable column
            'active'     => new ModelAttributeData(['name' => 'active']),
            // Exclude orderable column
            'position'   => new ModelAttributeData(['name' => 'position']),
            // Exclude timestamps
            'created_at' => new ModelAttributeData(['name' => 'created_at']),
            'updated_at' => new ModelAttributeData(['name' => 'updated_at']),
            // Exclude extra stapler fields
            'image_content_type' => new ModelAttributeData(['name' => 'image_content_type']),
            'image_file_name'    => new ModelAttributeData(['name' => 'image_file_name']),
            'image_file_size'    => new ModelAttributeData(['name' => 'image_file_size']),
            'image_updated_at'   => new ModelAttributeData(['name' => 'image_updated_at']),
        ];
        $info->relations  = [
            'single' => new ModelRelationData([
                'name'         => 'single',
                'method'       => 'single',
                'type'         => RelationType::BELONGS_TO,
                'foreign_keys' => [
                    'test_id',
                ],
            ]),
        ];

        $info->form->fields = [];

        $step = new EnrichFormFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);

        static::assertEquals(['image', 'single'], array_keys($info->form->fields));
    }

    /**
     * @test
     */
    function it_does_not_exclude_the_model_key_if_it_is_not_auto_incrementing()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display1', 'display2');
        $mockAttributeResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store1', 'store2');
        $mockAttributeResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);
        $mockRelationResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display3', 'display4');
        $mockRelationResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store3', 'store4');
        $mockRelationResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);

        $info = new ModelInformation;

        $info->model          = TestNotIncrementing::class;
        $info->original_model = TestNotIncrementing::class;
        $info->incrementing   = false;

        $info->attributes = [
            'id' => new ModelAttributeData(['name' => 'id']),
        ];

        $info->form->fields = [];

        $step = new EnrichFormFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);

        static::assertEquals(['id'], array_keys($info->form->fields));
    }

    /**
     * @test
     */
    function it_enriches_form_store_options_with_potentially_related_models_for_morph_to_relations()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockEnricher->shouldReceive('getAllModelInformation')
            ->andReturn(new Collection([
                // a model with a morphOne relation to the enriched model
                'test-model-a' => new ModelInformation([
                    'model'          => '\\Test\\ModelClass\\A',
                    'original_model' => '\\Test\\ModelClass\\A',
                    'relations'      => [
                        'morphone' => new ModelRelationData([
                            'name'         => 'morphone',
                            'method'       => 'morphone',
                            'type'         => RelationType::MORPH_ONE,
                            'relatedModel' => TestPost::class,
                        ]),
                    ],
                ]),
                'test-model-b' => new ModelInformation([
                    'model'          => '\\Test\\ModelClass\\B',
                    'original_model' => '\\Test\\ModelClass\\B',
                    'relations'      => [
                        'unrelated' => new ModelRelationData([
                            'name'         => 'morphone',
                            'method'       => 'morphone',
                            'type'         => RelationType::MORPH_ONE,
                            'relatedModel' => TestComment::class,
                        ]),
                    ],
                ]),
                // a model with a morphMany relation to the enriched model
                'test-model-c' => new ModelInformation([
                    'model'          => '\\Test\\ModelClass\\C',
                    'original_model' => '\\Test\\ModelClass\\C',
                    'relations'      => [
                        'morphmany' => new ModelRelationData([
                            'name'         => 'morphmany',
                            'method'       => 'morphmany',
                            'type'         => RelationType::MORPH_MANY,
                            'relatedModel' => TestPost::class,
                        ]),
                    ],
                ]),
            ]));

        $mockRelationResolver->shouldReceive('determineFormDisplayStrategy')->andReturn('display1', 'display2');
        $mockRelationResolver->shouldReceive('determineFormStoreStrategy')->andReturn('store1', 'store2');
        $mockRelationResolver->shouldReceive('determineFormStoreOptions')->andReturn([]);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        // Hidden attributes and foreign key columns should not be included
        $info->attributes = [
            'morphable_id'   => new ModelAttributeData(['name' => 'morphable_id']),
            'morphable_type' => new ModelAttributeData(['name' => 'morphable_type']),
        ];
        $info->relations  = [
            'morphtest' => new ModelRelationData([
                'name'         => 'morphtest',
                'method'       => 'morphtest',
                'type'         => RelationType::MORPH_TO,
                'foreign_keys' => [
                    'morphable_id',
                    'morphable_type',
                ],
            ]),
            'morphdefined' => new ModelRelationData([
                'name'         => 'morphdefined',
                'method'       => 'morphdefined',
                'type'         => RelationType::MORPH_TO,
                'foreign_keys' => [
                    'morphabledefined_id',
                    'morphabledefined_type',
                ],
                'morphModels' => [
                    '\\Test\\ModelClass\\X' => [],
                    '\\Test\\ModelClass\\Y' => [],
                ],
            ]),
        ];

        $info->form->fields = [];

        $step = new EnrichFormFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);


        static::assertEquals(['morphtest', 'morphdefined'], array_keys($info->form->fields));

        /** @var ModelFormFieldData $field */
        $field = $info->form->fields['morphtest'];
        static::assertInstanceOf(ModelFormfieldData::class, $field);
        static::assertEquals('morphtest', $field->key);
        static::assertEquals('display1', $field->display_strategy);
        static::assertEquals('store1', $field->store_strategy);
        static::assertEquals(
            [
                'models' => [
                    '\\Test\\ModelClass\\A' => [],
                    '\\Test\\ModelClass\\C' => [],
                ]
            ],
            $field->options
        );

        $field = $info->form->fields['morphdefined'];
        static::assertInstanceOf(ModelFormfieldData::class, $field);
        static::assertEquals('morphdefined', $field->key);
        static::assertEquals('display2', $field->display_strategy);
        static::assertEquals('store2', $field->store_strategy);
        static::assertEquals(
            [
                'models' => [
                    '\\Test\\ModelClass\\X' => [],
                    '\\Test\\ModelClass\\Y' => [],
                ]
            ],
            $field->options
        );
    }

    /**
     * @test
     */
    function it_throws_a_contextually_enriched_exception_if_form_field_enrichment_fails()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $step = new EnrichFormFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        $info = new ModelInformation;

        /** @var Mockery\Mock $fieldDataMock */
        $fieldDataMock = Mockery::mock(ModelFormFieldData::class);
        $fieldDataMock->shouldReceive('merge')->andThrow(new \Exception('testing'));

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->form->fields = [
            'title' => $fieldDataMock,
        ];

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('form.fields', $e->getSection());
            static::assertEquals('title', $e->getKey());
        }
    }

    /**
     * @return ModelInformationEnricherInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockEnricher()
    {
        return Mockery::mock(ModelInformationEnricherInterface::class);
    }

    /**
     * @return AttributeStrategyResolver|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockAttributeResolver()
    {
        return Mockery::mock(AttributeStrategyResolver::class);
    }

    /**
     * @return RelationStrategyResolver|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockRelationResolver()
    {
        return Mockery::mock(RelationStrategyResolver::class);
    }

}
