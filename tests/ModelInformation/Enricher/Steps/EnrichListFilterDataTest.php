<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeStrategyResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichListFilterData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\FilterStrategy;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

class EnrichListFilterDataTest extends TestCase
{

    /**
     * @test
     */
    function it_fills_list_filters_based_on_attributes_if_none_set_and_configured_to()
    {
        $this->app['config']->set('cms-models.analyzer.filters.single-any-string', false);

        $mockEnricher = $this->getMockEnricher();

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'active'       => new ModelAttributeData([
                'name' => 'active',
                'cast' => AttributeCast::BOOLEAN,
            ]),
            'type'         => new ModelAttributeData([
                'name'   => 'type',
                'cast'   => AttributeCast::STRING,
                'type'   => 'enum',
                'values' => ['a', 'b'],
            ]),
            'title'        => new ModelAttributeData([
                'name' => 'title',
                'cast' => AttributeCast::STRING,
                'type' => 'varchar',
            ]),
            'dob'          => new ModelAttributeData([
                'name' => 'dob',
                'cast' => AttributeCast::DATE,
                'type' => 'date',
            ]),
            'unfilterable' => new ModelAttributeData([
                'name' => 'unfilterable',
                'cast' => AttributeCast::STAPLER_ATTACHMENT,
            ]),
            'test_id'      => new ModelAttributeData(['name' => 'test_id']),
            'test2_id'     => new ModelAttributeData(['name' => 'test_id']),
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
            'through' => new ModelRelationData([
                'name'         => 'through',
                'method'       => 'through',
                'type'         => RelationType::BELONGS_TO_THROUGH,
                'foreign_keys' => [
                    'test2_id',
                ],
            ]),
        ];

        $info->list->filters = [];

        $step = new EnrichListFilterData($mockEnricher);
        $step->enrich($info, []);

        static::assertEquals(['active', 'type', 'title', 'dob'], array_keys($info->list->filters));

        /** @var ModelListFilterData $filter */
        $filter = $info->list->filters['active'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('active', $filter->target);
        static::assertEquals(FilterStrategy::BOOLEAN, $filter->strategy);

        $filter = $info->list->filters['type'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('type', $filter->source);
        static::assertEquals(FilterStrategy::DROPDOWN, $filter->strategy);
        static::assertEquals(['a', 'b'], $filter->options['values']);

        $filter = $info->list->filters['title'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('title', $filter->target);
        static::assertEquals(FilterStrategy::STRING, $filter->strategy);

        $filter = $info->list->filters['dob'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('dob', $filter->target);
        static::assertEquals(FilterStrategy::DATE, $filter->strategy);
    }

    /**
     * @test
     */
    function it_sets_a_single_any_text_filter_if_no_filters_set_and_configured_to()
    {
        $this->app['config']->set('cms-models.analyzer.filters.single-any-string', true);

        $mockEnricher = $this->getMockEnricher();

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->attributes = [
            'title'   => new ModelAttributeData(['name' => 'title']),
            'name'    => new ModelAttributeData(['name' => 'name', 'hidden' => true]),
            'active'  => new ModelAttributeData(['name' => 'active']),
        ];

        $info->list->filters = [];

        $step = new EnrichListFilterData($mockEnricher);
        $step->enrich($info, []);


        static::assertEquals(['any'], array_keys($info->list->filters));

        /** @var ModelListFilterData $filter */
        $filter = $info->list->filters['any'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('*', $filter->target);
        static::assertEquals('string-split', $filter->strategy);
    }

    /**
     * @test
     */
    function it_enriches_set_list_filters_based_on_attributes()
    {
        $mockEnricher = $this->getMockEnricher();

        $info = new ModelInformation;

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->attributes      = [
            'active'   => new ModelAttributeData([
                'name' => 'active',
                'cast' => AttributeCast::BOOLEAN,
            ]),
            'type'    => new ModelAttributeData([
                'name'   => 'type',
                'cast'   => AttributeCast::STRING,
                'type'   => 'enum',
                'values' => ['a', 'b'],
            ]),
            'title'    => new ModelAttributeData([
                'name' => 'title',
                'cast' => AttributeCast::STRING,
                'type' => 'varchar',
            ]),
            'dob'    => new ModelAttributeData([
                'name' => 'dob',
                'cast' => AttributeCast::DATE,
                'type' => 'date',
            ]),
            'test_id' => new ModelAttributeData(['name' => 'test_id']),
        ];
        $info->relations = [
            'single' => new ModelRelationData([
                'name'         => 'single',
                'method'       => 'single',
                'type'         => RelationType::BELONGS_TO_THROUGH,
                'foreign_keys' => [
                    'test_id',
                ],
            ]),
        ];

        $info->list->filters = [
            'active' => new ModelListFilterData(),
            'type'   => new ModelListFilterData(['strategy' => 'alt', 'target' => 'testing']),
            'new'    => new ModelListFilterData(['strategy' => 'filter', 'target' => 'new']),
        ];

        $step = new EnrichListFilterData($mockEnricher);
        $step->enrich($info, []);


        static::assertEquals(['active', 'type', 'new'], array_keys($info->list->filters));

        /** @var ModelListFilterData $filter */
        $filter = $info->list->filters['active'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('active', $filter->target);
        static::assertEquals(FilterStrategy::BOOLEAN, $filter->strategy);

        $filter = $info->list->filters['type'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('testing', $filter->target);
        static::assertEquals('alt', $filter->strategy);

        $filter = $info->list->filters['new'];
        static::assertInstanceOf(ModelListFilterData::class, $filter);
        static::assertEquals('new', $filter->target);
        static::assertEquals('filter', $filter->strategy);
    }

    /**
     * @test
     */
    function it_throws_a_contextually_enriched_exception_if_list_filter_data_set_is_incomplete()
    {
        $mockEnricher = $this->getMockEnricher();

        $info = new ModelInformation;

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;
        $info->list->filters = [
            'title' => new ModelListFilterData([
                'strategy' => 'test',
            ]),
        ];

        $step = new EnrichListFilterData($mockEnricher);

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('list.filters', $e->getSection());
            static::assertEquals('title', $e->getKey());
        }
    }

    /**
     * @test
     */
    function it_throws_a_contextually_enriched_exception_if_incomplete_list_filter_data_matches_unsupported_attribute()
    {
        $mockEnricher = $this->getMockEnricher();

        $info = new ModelInformation;

        $info->model           = TestPost::class;
        $info->original_model  = TestPost::class;

        $info->attributes = [
            'title'    => new ModelAttributeData([
                'name' => 'title',
                'cast' => AttributeCast::STAPLER_ATTACHMENT,
            ]),
        ];

        $info->list->filters = [
            'title' => new ModelListFilterData([
                'strategy' => 'test',
            ]),
        ];

        $step = new EnrichListFilterData($mockEnricher);

        try {
            $step->enrich($info, []);

            static::fail('Exception should have been thrown');

        } catch (\Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertEquals('list.filters', $e->getSection());
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
