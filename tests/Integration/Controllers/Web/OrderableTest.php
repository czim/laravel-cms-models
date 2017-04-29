<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Support\Enums\OrderablePosition;
use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class OrderableTest
 *
 * Tests for simple orderable model functions.
 *
 * @group integration
 * @group controllers
 */
class OrderableTest extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testauthor';


    /**
     * @test
     */
    function it_shows_a_list_of_models_as_an_orderable_listing()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        // Check if we have orderable column cells
        static::assertCount(2, $this->crawler()->filter('td.column-orderable'));

        // Check if new sort order is by position
        $rows = $this->crawler()->filter('tr.records-row');
        static::assertEquals(1, $rows->first()->attr('data-id'), 'Order incorrect');
        static::assertEquals(2, $rows->last()->attr('data-id'), 'Order incorrect');
    }
    
    /**
     * @test
     * @depends it_shows_a_list_of_models_as_an_orderable_listing
     */
    function it_updates_the_position_for_an_orderable_record()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        $token = $this->crawler()->filter('meta[name="csrf-token"]')->first()->attr('content');

        $this->route('POST', static::ROUTE_BASE . '.position', [2], [
            '_method'  => 'put',
            '_token'   => $token,
            'position' => OrderablePosition::TOP,
        ], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => true]);

        // Check if order is now altered
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        $rows = $this->crawler()->filter('tr.records-row');
        static::assertEquals(2, $rows->first()->attr('data-id'), 'Order incorrect');
        static::assertEquals(1, $rows->last()->attr('data-id'), 'Order incorrect');
    }

}
