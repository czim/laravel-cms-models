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
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        // Check if we have activation logic column cells
        static::assertCount(3, $this->crawler()->filter('td.column-activate'));

        // Check if active status is correct
        $this->assertHtmlElementInResponse('tr.records-row[data-id=1] .activate-toggle');
        $this->assertHtmlElementInResponse('tr.records-row[data-id=2] .activate-toggle');
        $this->assertHtmlElementInResponse('tr.records-row[data-id=3] .activate-toggle');


        static::assertEquals(
            1,
            $this->crawler()->filter('tr.records-row[data-id=1] .activate-toggle')->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #1')
        );
        static::assertEquals(
            0,
            $this->crawler()->filter('tr.records-row[data-id=2] .activate-toggle')->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #2')
        );
        static::assertEquals(
            1,
            $this->crawler()->filter('tr.records-row[data-id=3] .activate-toggle')->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #3')
        );
    }
    
    /**
     * @test
     * @depends it_shows_a_list_of_models_as_an_activatable_listing
     */
    function it_toggles_an_activatable_record()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        $token = $this->getCsrfTokenFromResponse();

        // Deactivate the first record
        $this->route('POST', static::ROUTE_BASE . '.activate', [1], [
            '_method'  => 'put',
            '_token'   => $token,
            'activate' => false,
        ], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => true]);

        // Check if it is now deactivated
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        static::assertEquals(
            0,
            $this->crawler()->filter('tr.records-row[data-id=1] .activate-toggle')->attr('data-active'),
            $this->appendResponseHtml('Incorrect active state for #1')
        );
    }

}
