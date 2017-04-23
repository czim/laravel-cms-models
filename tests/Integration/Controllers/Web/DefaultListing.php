<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class DefaultListing
 *
 * Tests for simple model listings. Note browser/javascript functionality is deliberately not tested here.
 *
 * @group integration
 * @group controllers
 */
class DefaultListing extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';


    protected $customModelConfiguration = [
        'it_redirects_to_form_page_if_single_mode_is_enabled' => [
            'test-post' => [
                'single' => true,
            ],
        ],
    ];

    /**
     * @test
     */
    function it_shows_a_list_of_models()
    {
        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200);

        static::assertHtmlElementInResponse('tr.records-row[data-id=3]', 'Expected model record not found');
        static::assertHtmlElementInResponse('tr.records-row[data-id=2]', 'Expected model record not found');
        static::assertHtmlElementInResponse('tr.records-row[data-id=1]', 'Expected model record not found');

        static::assertTrue($this->listingHasColumnTextForRecord(1, 'Some Basic Title'), 'ID #1 title not present');
        static::assertTrue($this->listingHasColumnTextForRecord(2, 'Elaborate Alternative Title'), 'ID #2 title not present');
        static::assertTrue($this->listingHasColumnTextForRecord(3, 'Surprising Testing Title'), 'ID #3 title not present');
    }

    /**
     * @test
     */
    function it_shows_a_paginated_list_of_models()
    {
        // Modify page size to force 2 pages
        $this->app['config']->set('cms-models.strategies.list.page-size', 2);

        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200)
            ->seeRouteIs(static::ROUTE_BASE . '.index')
            // There should be links to page 2
            ->seeLink('2', route(static::ROUTE_BASE . '.index', ['page' => 2]))
            ->seeLink('»', route(static::ROUTE_BASE . '.index', ['page' => 2]))
            // There should be no page 3
            ->seeLink('3', route(static::ROUTE_BASE . '.index', ['page' => 3]), true);

        // Make sure the correct model records are present
        static::assertHtmlElementInResponse('tr.records-row[data-id=3]', 'Expected model record not found');
        static::assertHtmlElementInResponse('tr.records-row[data-id=2]', 'Expected model record not found');
        static::assertNotHtmlElementInResponse('tr.records-row[data-id=1]', 'Third record should not be present');

        // Load the next page
        $this
            ->visitRoute(static::ROUTE_BASE . '.index', ['page' => 2])
            ->seeStatusCode(200)
            ->seeRouteIs(static::ROUTE_BASE . '.index', ['page' => 2])
            // There should be links back to page 1
            ->seeLink('1', route(static::ROUTE_BASE . '.index', ['page' => 1]))
            ->seeLink('«', route(static::ROUTE_BASE . '.index', ['page' => 1]));
    }

    /**
     * @test
     */
    function it_shows_a_paginated_list_of_models_with_custom_page_size()
    {
        // Submit form to set custom page size
        $this->app['config']->set('cms-models.strategies.list.page-size-options', [ 10, 2, 50 ]);

        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200)
            ->seeRouteIs(static::ROUTE_BASE . '.index')
            // There should not be any pages beyond the first by default
            ->seeLink('2', route(static::ROUTE_BASE . '.index', ['page' => 2]), true);

        // Make sure the page size options are present
        static::assertHtmlElementInResponse('#input-pagination-page-size option[value=10]');
        static::assertHtmlElementInResponse('#input-pagination-page-size option[value=2]');
        static::assertHtmlElementInResponse('#input-pagination-page-size option[value=50]');

        // Submit a form with a smaller page size
        $this->makeRequestUsingForm(
            $this->crawler()->filter('#form-pagination-page-size')->form()->setValues(['pagesize' => 2])
        );

        // Verify page is limited to new size
        $this
            ->seeStatusCode(200)
            // There should be a link to page 2
            ->seeLink('2', route(static::ROUTE_BASE . '.index', ['page' => 2]));

        static::assertNotHtmlElementInResponse('tr.records-row[data-id=1]', 'Third record should not be present');
    }

    /**
     * @test
     * @see $customModelConfiguration
     */
    function it_redirects_to_form_page_if_single_mode_is_enabled()
    {
        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200);

        static::assertHtmlElementInResponse('form.model-form[data-id=1]', 'Expected form for model #1');
    }
    
    /**
     * @test
     */
    function it_shows_a_list_of_models_with_custom_sorting()
    {
        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200);

        // Check if current sort order is ID descending
        $rows = $this->crawler()->filter('tr.records-row');
        static::assertEquals(3, $rows->first()->attr('data-id'), 'Order incorrect for default sort');
        static::assertEquals(2, $rows->eq(1)->attr('data-id'), 'Order incorrect for default sort');
        static::assertEquals(1, $rows->last()->attr('data-id'), 'Order incorrect for default sort');

        $this->assertHtmlElementInResponse(
            'a.sort.active[href="?sort=id&sortdir=asc"]',
            'ID active ascending link should be present'
        );

        // Select title for sorting
        $this
            ->visitRoute(static::ROUTE_BASE . '.index', ['sort' => 'title'])
            ->seeStatusCode(200);

        // Check if new sort order is title ascending
        $rows = $this->crawler()->filter('tr.records-row');
        static::assertEquals(2, $rows->first()->attr('data-id'), 'Order incorrect for title sort');
        static::assertEquals(1, $rows->eq(1)->attr('data-id'), 'Order incorrect for title sort');
        static::assertEquals(3, $rows->last()->attr('data-id'), 'Order incorrect for title sort');

        $this->assertHtmlElementInResponse(
            'a.sort.active[href="?sort=title&sortdir=desc"]',
            'Title active descending link should be present'
        );

        // Select descending order for title for sorting
        $this
            ->visitRoute(static::ROUTE_BASE . '.index', ['sort' => 'title', 'sortdir' => 'desc'])
            ->seeStatusCode(200);

        // Check if new sort order is title descending
        $rows = $this->crawler()->filter('tr.records-row');
        static::assertEquals(3, $rows->first()->attr('data-id'), 'Order incorrect for title desc sort');
        static::assertEquals(1, $rows->eq(1)->attr('data-id'), 'Order incorrect for title desc sort');
        static::assertEquals(2, $rows->last()->attr('data-id'), 'Order incorrect for title desc sort');
    }

    /**
     * @test
     * @depends it_shows_a_list_of_models_with_custom_sorting
     */
    function it_remembers_page_and_sorting_in_session()
    {
        $this->app['config']->set('cms-models.strategies.list.page-size', 2);

        // Request with specific sorting
        $this
            ->visitRoute(static::ROUTE_BASE . '.index', ['sort' => 'title', 'sortdir' => 'desc'])
            ->seeStatusCode(200);

        // Request specific page, using session-set sorting
        $this
            ->visitRoute(static::ROUTE_BASE . '.index', ['page' => 2])
            ->seeStatusCode(200);

        // Only #2 (sorted last for title desc) should be on page 2
        static::assertHtmlElementInResponse('tr.records-row[data-id=2]', 'Expected model record not found');
        static::assertNotHtmlElementInResponse('tr.records-row[data-id=3]', 'Model from first page should not be present');
        static::assertNotHtmlElementInResponse('tr.records-row[data-id=1]', 'Model from first page should not be present');

        // Request without parameters, using the session-set page & sorting
        $this
            ->visitRoute(static::ROUTE_BASE . '.index')
            ->seeStatusCode(200);

        // Only #2 (sorted last for title desc) should be on page 2
        static::assertHtmlElementInResponse('tr.records-row[data-id=2]', 'Expected model record not found');
        static::assertNotHtmlElementInResponse('tr.records-row[data-id=3]', 'Model from first page should not be present');
        static::assertNotHtmlElementInResponse('tr.records-row[data-id=1]', 'Model from first page should not be present');
    }

    /**
     * Returns a list of strings with contents from the listing, for a given row (by record ID).
     *
     * @param mixed $id
     * @return string[]
     */
    protected function getColumnContentsForListingRow($id)
    {
        $columns = $this->crawler()->filter('tr.records-row[data-id="' . $id .'"] td.column');

        $content = [];

        foreach ($columns as $column) {
            /** @var \DOMElement $column */
            $content[] = $column->textContent;
        }

        return $content;
    }

    /**
     * Returns whether column text exists for a given record ID row.
     *
     * @param      $id
     * @param      $text
     * @param null $columnIndex
     * @return bool
     */
    protected function listingHasColumnTextForRecord($id, $text, $columnIndex = null)
    {
        $columns = $this->getColumnContentsForListingRow($id);
        $columns = array_map('trim', $columns);

        if ( ! count($columns)) {
            return false;
        }

        if (null !== $columnIndex) {
            return array_get($columns, $columnIndex) === $text;
        }

        return in_array($text, $columns);
    }

}
