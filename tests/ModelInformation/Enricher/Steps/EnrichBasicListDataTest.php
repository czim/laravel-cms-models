<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelActionReferenceData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichBasicListData;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

class EnrichBasicListDataTest extends TestCase
{

    // ------------------------------------------------------------------------------
    //      Default Sorting Order
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_sets_the_sorting_order_not_overwriting_specified_default()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model              = TestPost::class;
        $info->original_model     = TestPost::class;
        $info->list->default_sort = 'test';

        $step->enrich($info, []);

        static::assertEquals('test', $info->list->default_sort);
    }

    /**
     * @test
     */
    function it_sets_the_sorting_order_to_orderable_column_if_available()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model              = TestPost::class;
        $info->original_model     = TestPost::class;
        $info->list->orderable    = true;
        $info->list->order_column = 'position';

        $step->enrich($info, []);

        static::assertEquals('position', $info->list->default_sort);
    }

    /**
     * @test
     */
    function it_sets_the_sorting_order_to_timestamp_created_if_available()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model             = TestPost::class;
        $info->original_model    = TestPost::class;
        $info->timestamps        = true;
        $info->timestamp_created = 'created_at';

        $step->enrich($info, []);

        static::assertEquals('created_at', $info->list->default_sort);
    }

    /**
     * @test
     */
    function it_sets_the_sorting_order_to_fallback_to_primary_key_if_auto_incrementing()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;
        $info->timestamps     = false;
        $info->incrementing   = true;

        $step->enrich($info, []);

        static::assertEquals('id', $info->list->default_sort);
    }

    /**
     * @test
     */
    function it_does_not_set_the_sorting_order_if_there_is_no_safe_fallback()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;
        $info->timestamps     = false;
        $info->incrementing   = false;

        $step->enrich($info, []);

        static::assertEmpty($info->list->default_sort);
    }

    // ------------------------------------------------------------------------------
    //      Reference Source
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_sets_the_reference_source_not_overwriting_specified_source()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model             = TestPost::class;
        $info->original_model    = TestPost::class;
        $info->reference->source = 'test';

        $step->enrich($info, []);

        static::assertEquals('test', $info->reference->source);
    }

    /**
     * @test
     */
    function it_sets_the_reference_source_by_picking_the_best_available_configured_matching_attribute()
    {
        $mockEnricher = $this->getMockEnricher();

        $this->app['config']->set('cms-models.analyzer.reference.sources', [
            'unmatched',
            'testing',
        ]);

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;
        $info->attributes     = [
            'not_this_one' => new ModelAttributeData,
            'testing'      => new ModelAttributeData,
        ];

        $step->enrich($info, []);

        static::assertEquals('testing', $info->reference->source);
    }


    // ------------------------------------------------------------------------------
    //      Default Row Action
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_uses_default_edit_and_show_actions_if_none_set_and_configured_to()
    {
        $this->app['config']->set('cms-models.defaults.default-listing-action-edit', true);
        $this->app['config']->set('cms-models.defaults.default-listing-action-show', true);

        /** @var RouteHelperInterface|Mockery\Mock $routeHelper */
        $routeHelper = Mockery::mock(RouteHelperInterface::class);
        $routeHelper->shouldReceive('getRouteSlugForModelClass')->andReturn('app-models-test');
        $routeHelper->shouldReceive('getPermissionPrefixForModelSlug')->with('app-models-test')->andReturn('models.app-models-test.');

        $this->app->instance(RouteHelperInterface::class, $routeHelper);

        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $step->enrich($info, []);

        static::assertCount(2, $info->list->default_action);
        static::assertInstanceof(ModelActionReferenceData::class, $info->list->default_action[0]);
        static::assertEquals('edit', $info->list->default_action[0]['strategy']);
        static::assertEquals('models.app-models-test.edit', $info->list->default_action[0]['permissions']);
        static::assertInstanceof(ModelActionReferenceData::class, $info->list->default_action[1]);
        static::assertEquals('show', $info->list->default_action[1]['strategy']);
        static::assertEquals('models.app-models-test.show', $info->list->default_action[1]['permissions']);
    }

    /**
     * @test
     */
    function it_does_not_enricht_default_actions_if_they_are_set()
    {
        $this->app['config']->set('cms-models.defaults.default-listing-action-edit', true);
        $this->app['config']->set('cms-models.defaults.default-listing-action-show', true);

        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichBasicListData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;
        $info->list->default_action = [
            [
                'strategy'    => 'show',
                'permissions' => 'testing',
            ]
        ];

        $step->enrich($info, []);

        static::assertCount(1, $info->list->default_action);
        static::assertInstanceof(ModelActionReferenceData::class, $info->list->default_action[0]);
        static::assertEquals('show', $info->list->default_action[0]['strategy']);
        static::assertEquals('testing', $info->list->default_action[0]['permissions']);
    }


    /**
     * @return ModelInformationEnricherInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockEnricher()
    {
        return Mockery::mock(ModelInformationEnricherInterface::class);
    }

}
