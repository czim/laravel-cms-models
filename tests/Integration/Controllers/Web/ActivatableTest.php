<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class ActivatableTest
 *
 * Tests for simple activatable model functions.
 *
 * @group integration
 * @group controllers
 */
class ActivatableTest extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';

    protected $customModelConfiguration = [
        'it_shows_a_list_of_models_as_an_activatable_listing' => [
            'test-post' => [
                'list' => [
                    'activatable'   => true,
                    'active_column' => 'checked',
                ],
            ],
        ],
        'it_toggles_an_activatable_record' => [
            'test-post' => [
                'list' => [
                    'activatable'   => true,
                    'active_column' => 'checked',
                ],
            ],
        ],
    ];


    /**
     * @test
     */
    function it_shows_a_list_of_models_as_an_activatable_listing()
    {
        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200);

        // Check if we have activation logic column cells
        static::assertCount(3, $this->crawler()->filter('td.column-activate'));

        // Check if active status is correct
        $rows = $this->crawler()->filter('tr.records-row .activate-toggle');
        static::assertEquals(
            1,
            $rows->first()->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #1'));
        static::assertEquals(
            0,
            $rows->eq(1)->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #2'));
        static::assertEquals(
            1,
            $rows->last()->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #3')
        );
    }
    
    /**
     * @test
     * @depends it_shows_a_list_of_models_as_an_activatable_listing
     */
    function it_toggles_an_activatable_record()
    {
        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200);

        $token = $this->crawler()->filter('meta[name="csrf-token"]')->first()->attr('content');

        // Deactivate the first record
        $response = $this->route('POST', static::ROUTE_BASE . '.activate', [1], [
            '_method'  => 'put',
            '_token'   => $token,
            'activate' => false,
        ]);

        static::assertTrue($response->isRedirection(), 'Should be redirected after active toggle');

        // Check if it is now deactivated
        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200);

        $rows = $this->crawler()->filter('tr.records-row .activate-toggle');
        static::assertEquals(
            0,
            $rows->first()->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #1')
        );
    }

}
