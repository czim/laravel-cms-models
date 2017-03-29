<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeStrategyResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportColumnData;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportStrategyData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichExportColumnData;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

class EnrichExportColumnDataTest extends TestCase
{

    /**
     * @test
     */
    function it_fills_default_export_columns_based_on_attributes_if_none_set()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineExportColumnStrategy')->times(2)->andReturn('test', 'test2');

        $step = new EnrichExportColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        $info = new ModelInformation;
        $info->model = TestPost::class;
        $info->original_model = TestPost::class;

        // Hidden attributes and foreign key columns should not be included
        $info->attributes = [
            'title'   => new ModelAttributeData(['name' => 'title']),
            'name'    => new ModelAttributeData(['name' => 'name', 'hidden' => true]),
            'active'  => new ModelAttributeData(['name' => 'active']),
            'test_id' => new ModelAttributeData(['name' => 'test_id']),
        ];
        $info->relations = [
            'test' => new ModelRelationData([
                'name'         => 'test',
                'method'       => 'test',
                'type'         => RelationType::BELONGS_TO,
                'foreign_keys' => [
                    'test_id',
                ],
            ]),
            'many' => new ModelRelationData([
                'name'         => 'many',
                'method'       => 'many',
                'type'         => RelationType::HAS_MANY,
            ]),
        ];

        $info->export->columns = [];

        $step->enrich($info, []);


        static::assertEquals(['title', 'active'], array_keys($info->export->columns));

        /** @var ModelExportColumnData */
        $column = $info->export->columns['title'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('title', $column->source);
        static::assertEquals('test', $column->strategy);

        $column = $info->export->columns['active'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('active', $column->source);
        static::assertEquals('test2', $column->strategy);
    }

    /**
     * @test
     */
    function it_enriches_set_export_columns_based_on_attributes()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $mockAttributeResolver->shouldReceive('determineExportColumnStrategy')->times(2)->andReturn('test', 'test2');

        $step = new EnrichExportColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        $info = new ModelInformation;
        $info->model = TestPost::class;
        $info->original_model = TestPost::class;
        $info->attributes = [
            'title'  => new ModelAttributeData(['name' => 'title']),
            'name'   => new ModelAttributeData(['name' => 'name']),
            'active' => new ModelAttributeData(['name' => 'active']),
        ];
        $info->export->columns = [
            'name'   => new ModelExportColumnData(['hide' => true]),
            'active' => new ModelExportColumnData(['strategy' => 'alt', 'header' => 'testing']),
            'new'    => new ModelExportColumnData(['source' => 'custom_column']),
        ];

        $step->enrich($info, []);


        static::assertEquals(['name', 'active', 'new'], array_keys($info->export->columns));

        /** @var ModelExportColumnData */
        $column = $info->export->columns['name'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('name', $column->source);
        static::assertEquals('test', $column->strategy);
        static::assertTrue($column->hide);

        $column = $info->export->columns['active'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('active', $column->source);
        static::assertEquals('testing', $column->header);
        static::assertEquals('alt', $column->strategy);

        $column = $info->export->columns['new'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('custom_column', $column->source);
    }

    /**
     * @test
     */
    function it_enriches_set_export_columns_of_an_export_strategy()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        // One attribute, enriched once for default and once for 'csv' strategy, means two calls
        $mockAttributeResolver->shouldReceive('determineExportColumnStrategy')->andReturn('test');

        $step = new EnrichExportColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        $info = new ModelInformation;
        $info->model = TestPost::class;
        $info->original_model = TestPost::class;
        $info->attributes = [
            'name' => new ModelAttributeData(['name' => 'name']),
        ];
        $info->export->strategies = [
            'csv' => new ModelExportStrategyData([
                'columns' => [
                    'name' => new ModelExportColumnData(['hide' => true]),
                    'new'  => new ModelExportColumnData(['source' => 'custom_column']),
                ],
            ]),
        ];

        $step->enrich($info, []);


        static::assertEquals(['csv'], array_keys($info->export->strategies));
        /** @var ModelExportStrategyData $strategy */
        $strategy = $info->export->strategies['csv'];
        static::assertInstanceOf(ModelExportStrategyData::class, $strategy);
        static::assertEquals(['name', 'new'], array_keys($strategy->columns));

        /** @var ModelExportColumnData */
        $column = $strategy->columns['name'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('name', $column->source);
        static::assertEquals('test', $column->strategy);
        static::assertTrue($column->hide);

        $column = $strategy->columns['new'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('custom_column', $column->source);
    }

    /**
     * @test
     */
    function it_uses_default_export_columns_for_an_export_strategy_without_set_columns()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        // One attribute, enriched once for default and once for 'csv' strategy, means two calls
        $mockAttributeResolver->shouldReceive('determineExportColumnStrategy')->andReturn('test');

        $step = new EnrichExportColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        $info = new ModelInformation;
        $info->model = TestPost::class;
        $info->original_model = TestPost::class;
        $info->attributes = [
            'name' => new ModelAttributeData(['name' => 'name']),
        ];
        $info->export->strategies = [
            'csv' => new ModelExportStrategyData([
                'columns' => [],
            ]),
        ];

        $step->enrich($info, []);


        static::assertEquals(['csv'], array_keys($info->export->strategies));
        /** @var ModelExportStrategyData $strategy */
        $strategy = $info->export->strategies['csv'];
        static::assertInstanceOf(ModelExportStrategyData::class, $strategy);
        static::assertEquals(['name'], array_keys($strategy->columns));

        /** @var ModelExportColumnData */
        $column = $strategy->columns['name'];
        static::assertInstanceOf(ModelExportColumnData::class, $column);
        static::assertEquals('name', $column->source);
        static::assertEquals('test', $column->strategy);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_custom_export_column_data_has_no_source()
    {
        $mockEnricher          = $this->getMockEnricher();
        $mockAttributeResolver = $this->getMockAttributeResolver();
        $mockRelationResolver  = $this->getMockRelationResolver();

        $step = new EnrichExportColumnData($mockEnricher, $mockAttributeResolver, $mockRelationResolver);

        $info = new ModelInformation;
        $info->model = TestPost::class;
        $info->original_model = TestPost::class;
        $info->export->columns = [
            'name'   => new ModelExportColumnData(['hide' => true, 'source' => null]),
        ];

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('export.columns', $e->getSection());
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
