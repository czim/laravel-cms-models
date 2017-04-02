<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeStrategyResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\ModelInformation\Data\Show\ModelShowFieldData;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichShowFieldData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class EnrichShowFieldDataTest
 *
 * @group enrichment
 */
class EnrichShowFieldDataTest extends TestCase
{

    /**
     * @test
     */
    function it_fills_show_fields_based_on_attributes_if_none_set()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display1', 'display2');
        $mockRelationResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display3', 'display4');

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        // Hidden attributes and foreign key columns should not be included
        $info->attributes = [
            'title'   => new ModelAttributeData(['name' => 'title']),
            'name'    => new ModelAttributeData(['name' => 'name', 'hidden' => true]),
            'active'  => new ModelAttributeData(['name' => 'active']),
            'test_id' => new ModelAttributeData(['name' => 'test_id']),
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

        $info->show->fields = [];

        $step = new EnrichShowFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);


        static::assertEquals(['title', 'active'], array_keys($info->show->fields));

        /** @var ModelShowFieldData $field */
        $field = $info->show->fields['title'];
        static::assertInstanceOf(ModelShowFieldData::class, $field);
        static::assertEquals('title', $field->source);
        static::assertEquals('display1', $field->strategy);

        $field = $info->show->fields['active'];
        static::assertInstanceOf(ModelShowFieldData::class, $field);
        static::assertEquals('active', $field->source);
        static::assertEquals('display2', $field->strategy);
    }

    /**
     * @test
     */
    function it_enriches_set_show_fields_based_on_attributes()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display1', 'display2');
        $mockRelationResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display3');

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

        $info->show->fields = [
            'name'   => new ModelShowFieldData(),
            'active' => new ModelShowFieldData(['strategy' => 'alt', 'source' => 'testing']),
            'new'    => new ModelShowFieldData(['strategy' => 'display', 'source' => 'new']),
            'many'   => new ModelShowFieldData(),
        ];

        $step = new EnrichShowFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);


        static::assertEquals(['name', 'active', 'new', 'many'], array_keys($info->show->fields));

        /** @var ModelShowFieldData $field */
        $field = $info->show->fields['name'];
        static::assertInstanceOf(ModelShowFieldData::class, $field);
        static::assertEquals('name', $field->source);
        static::assertEquals('display1', $field->strategy);

        $field = $info->show->fields['active'];
        static::assertInstanceOf(ModelShowFieldData::class, $field);
        static::assertEquals('testing', $field->source);
        static::assertEquals('alt', $field->strategy);

        $field = $info->show->fields['new'];
        static::assertInstanceOf(ModelShowFieldData::class, $field);
        static::assertEquals('new', $field->source);
        static::assertEquals('display', $field->strategy);

        $field = $info->show->fields['many'];
        static::assertInstanceOf(ModelShowFieldData::class, $field);
        static::assertEquals('many', $field->source);
        static::assertEquals('display3', $field->strategy);
    }

    /**
     * @test
     */
    function it_excludes_attributes_that_should_not_be_displayed_by_default()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display1', 'display2');
        $mockRelationResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display3', 'display4');

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
            // Exclude hidden attributes
            'name'       => new ModelAttributeData(['name' => 'name', 'hidden' => true]),
            // Exclude foreign keys
            'test_id'    => new ModelAttributeData(['name' => 'test_id']),
            // Exclude activatable column
            'active'     => new ModelAttributeData(['name' => 'active']),
            // Exclude orderable column
            'position'   => new ModelAttributeData(['name' => 'position']),
            // Exclude text & blob fields
            'text'       => new ModelAttributeData(['name' => 'text', 'type' => 'text']),
            'blob'       => new ModelAttributeData(['name' => 'blob', 'type' => 'blob']),
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

        $info->show->fields = [];

        $step = new EnrichShowFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);

        static::assertEquals(['image'], array_keys($info->show->fields));
    }

    /**
     * @test
     */
    function it_throws_a_contextually_enriched_exception_if_show_field_data_set_is_incomplete()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $info = new ModelInformation;

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->show->fields = [
            'title' => new ModelShowFieldData([
                'source' => 'test',
            ]),
        ];

        $step = new EnrichShowFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('show.fields', $e->getSection());
            static::assertEquals('title', $e->getKey());
        }
    }

    /**
     * @test
     */
    function it_throws_a_contextually_enriched_exception_if_show_field_enrichment_fails()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $info = new ModelInformation;

        /** @var Mockery\Mock $fieldDataMock */
        $fieldDataMock = Mockery::mock(ModelShowFieldData::class);
        $fieldDataMock->shouldReceive('merge')->andThrow(new \Exception('testing'));

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->show->fields = [
            'title' => $fieldDataMock,
        ];

        $step = new EnrichShowFieldData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('show.fields', $e->getSection());
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
