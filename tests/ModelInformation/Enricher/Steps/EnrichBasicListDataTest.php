<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
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


    /**
     * @return ModelInformationEnricherInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockEnricher()
    {
        return Mockery::mock(ModelInformationEnricherInterface::class);
    }

}
