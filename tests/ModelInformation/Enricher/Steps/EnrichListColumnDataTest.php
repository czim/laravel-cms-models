<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeStrategyResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListColumnData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichListColumnData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class EnrichListColumnDataTest
 *
 * @group enrichment
 */
class EnrichListColumnDataTest extends TestCase
{

    /**
     * @test
     */
    function it_fills_list_columns_based_on_attributes_if_none_set()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display1', 'display2');
        $mockRelationResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display3', 'display4');

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

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

        $info->list->columns = [];

        $step = new EnrichListColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);


        static::assertEquals(['title', 'active'], array_keys($info->list->columns));

        /** @var ModelListColumnData $column */
        $column = $info->list->columns['title'];
        static::assertInstanceOf(ModelListColumnData::class, $column);
        static::assertEquals('title', $column->source);
        static::assertEquals('display1', $column->strategy);

        $column = $info->list->columns['active'];
        static::assertInstanceOf(ModelListColumnData::class, $column);
        static::assertEquals('active', $column->source);
        static::assertEquals('display2', $column->strategy);
    }

    /**
     * @test
     */
    function it_enriches_set_list_columns_based_on_attributes()
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

        $info->list->columns = [
            'name'   => new ModelListColumnData(),
            'active' => new ModelListColumnData(['strategy' => 'alt', 'source' => 'testing']),
            'new'    => new ModelListColumnData(['strategy' => 'display', 'source' => 'new']),
            'many'   => new ModelListColumnData(),
        ];

        $step = new EnrichListColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);


        static::assertEquals(['name', 'active', 'new', 'many'], array_keys($info->list->columns));

        /** @var ModelListColumnData $column */
        $column = $info->list->columns['name'];
        static::assertInstanceOf(ModelListColumnData::class, $column);
        static::assertEquals('name', $column->source);
        static::assertEquals('display1', $column->strategy);

        $column = $info->list->columns['active'];
        static::assertInstanceOf(ModelListColumnData::class, $column);
        static::assertEquals('testing', $column->source);
        static::assertEquals('alt', $column->strategy);

        $column = $info->list->columns['new'];
        static::assertInstanceOf(ModelListColumnData::class, $column);
        static::assertEquals('new', $column->source);
        static::assertEquals('display', $column->strategy);

        $column = $info->list->columns['many'];
        static::assertInstanceOf(ModelListColumnData::class, $column);
        static::assertEquals('many', $column->source);
        static::assertEquals('display3', $column->strategy);
    }

    /**
     * @test
     */
    function it_enriches_list_columns_with_sorting_and_sorting_direction_based_on_attribute_data()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display1', 'display2');
        $mockRelationResolver->shouldReceive('determineListDisplayStrategy')->andReturn('display3', 'display4');

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->incrementing = true;

        $info->attributes = [
            'id' => new ModelAttributeData([
                'name' => 'id',
                'cast' => AttributeCast::INTEGER,
            ]),
            'position' => new ModelAttributeData([
                'name' => 'position',
                'cast' => AttributeCast::INTEGER,
            ]),
            'active' => new ModelAttributeData([
                'name' => 'active',
                'cast' => AttributeCast::BOOLEAN,
            ]),
            'title' => new ModelAttributeData([
                'name' => 'title',
                'cast' => AttributeCast::STRING,
            ]),
            'body' => new ModelAttributeData([
                'name' => 'body',
                'cast' => AttributeCast::STRING,
                'type' => 'text',
            ]),
            'blob' => new ModelAttributeData([
                'name' => 'blob',
                'cast' => AttributeCast::STRING,
                'type' => 'blob',
            ]),
            'date' => new ModelAttributeData([
                'name' => 'date',
                'cast' => AttributeCast::DATE,
            ]),
        ];

        $info->list->columns = [
            'id'       => new ModelListColumnData,
            'position' => new ModelListColumnData,
            'active'   => new ModelListColumnData,
            'title'    => new ModelListColumnData,
            'body'     => new ModelListColumnData,
            'blob'     => new ModelListColumnData,
            'date'     => new ModelListColumnData,
        ];

        $step = new EnrichListColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);

        /** @var ModelListColumnData $column */
        $column = $info->list->columns['id'];
        static::assertTrue($column->sortable);
        static::assertEquals('desc', $column->sort_direction);

        $column = $info->list->columns['position'];
        static::assertTrue($column->sortable);
        static::assertEquals('asc', $column->sort_direction);

        $column = $info->list->columns['active'];
        static::assertTrue($column->sortable);
        static::assertEquals('desc', $column->sort_direction);

        $column = $info->list->columns['title'];
        static::assertTrue($column->sortable);
        static::assertEquals('asc', $column->sort_direction);

        $column = $info->list->columns['body'];
        static::assertNotTrue($column->sortable);

        $column = $info->list->columns['date'];
        static::assertTrue($column->sortable);
        static::assertEquals('desc', $column->sort_direction);
    }

    /**
     * @test
     */
    function it_excludes_attributes_that_should_not_be_editable_by_default()
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

        $info->list->columns = [];

        $step = new EnrichListColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);
        $step->enrich($info, []);

        static::assertEquals(['image'], array_keys($info->list->columns));
    }

    /**
     * @test
     */
    function it_throws_a_contextually_enriched_exception_if_list_column_data_set_is_incomplete()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $info = new ModelInformation;

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->list->columns = [
            'title' => new ModelListColumnData([
                'source' => 'test',
            ]),
        ];

        $step = new EnrichListColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('list.columns', $e->getSection());
            static::assertEquals('title', $e->getKey());
        }
    }

    /**
     * @test
     */
    function it_throws_a_contextually_enriched_exception_if_list_column_enrichment_fails()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $info = new ModelInformation;

        /** @var Mockery\Mock $fieldDataMock */
        $fieldDataMock = Mockery::mock(ModelListColumnData::class);
        $fieldDataMock->shouldReceive('merge')->andThrow(new \Exception('testing'));

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->list->columns = [
            'title' => $fieldDataMock,
        ];

        $step = new EnrichListColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('list.columns', $e->getSection());
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
