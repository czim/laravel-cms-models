<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class BasicListExportTest
 *
 * Tests for simple model listing exports.
 *
 * @group integration
 * @group controllers
 */
class BasicListExportTest extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';


    protected $customModelConfiguration = [
        'it_shows_export_links_on_listing_page' => [
            'test-post' => [
                'export' => [
                    'enable' => true,
                    'strategies' => [
                        'csv' => true,
                        'xml' => true,
                    ],
                ],
            ],
        ],
        'it_exports_listing_as_csv' => [
            'test-post' => [
                'export' => [
                    'enable' => true,
                    'strategies' => [
                        'csv' => true,
                    ],
                ],
            ],
        ],
    ];

    /**
     * @test
     */
    function it_shows_export_links_on_listing_page()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        static::assertHtmlElementInResponse(
            'a[href="http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testpost/export/csv"]',
            'Export link for CSV not present'
        );
        static::assertHtmlElementInResponse(
            'a[href="http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testpost/export/xml"]',
            'Export link for XML not present'
        );
    }

    /**
     * @test
     */
    function it_does_not_allow_export_if_not_enabled()
    {
        $this->call('GET', route(static::ROUTE_BASE . '.export', ['csv']));
        $this->seeStatusCode(403);
    }

    /**
     * @test
     */
    function it_exports_listing_as_csv()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        // Apply a filter so we only match one row
        $this->makeRequestUsingForm(
            $this->crawler()->filter('#filters-form')->form()->setValues(['filter[any]' => 'elaborate'])
        );
        $this->seeStatusCode(200);

        $this->call('GET', route(static::ROUTE_BASE . '.export', ['csv']));
        $this->seeStatusCode(200);

        /** @var BinaryFileResponse $response */
        $response = $this->response;

        // Check response
        static::assertInstanceOf(BinaryFileResponse::class, $response, 'Expected binary file response');
        static::assertInstanceOf(File::class, $response->getFile(), 'No file in response');
        static::assertEquals('text/plain', $response->getFile()->getMimeType(), 'Expected plain mimetype');
        static::assertEquals('csv', $response->getFile()->getExtension(), 'Expected CSV file extension');

        $path = $response->getFile()->getRealPath();

        static::assertFileExists($path, 'File does not exist');
        static::assertEquals('text/plain', mime_content_type($path), 'Mimetype incorrect');

        // Check contents
        $content = file_get_contents($path);
        $lines = array_filter(explode("\n", $content));

        static::assertCount(2, $lines, 'There should be 1 line and 1 header line');

        static::assertEquals(
            'Id;"Test genre id";Description;Type;Checked;Position;"Created at";"Updated at";Title;Body',
            $lines[0],
            'Header row does not match'
        );

        static::assertRegExp(
            '#^'
            . preg_quote('2;;"some alternative testing post";news;common.boolean.false;2;"')
            . '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}";"\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}'
            . preg_quote('";"Elaborate Alternative Title";"Donec nec metus urna. Tosti pancake frying pan tortellini Fusce ex massa, commodo ut rhoncus eu, iaculis sed quam."')
            . '$#',
            $lines[1],
            'Content row does not match'
        );
    }

}
