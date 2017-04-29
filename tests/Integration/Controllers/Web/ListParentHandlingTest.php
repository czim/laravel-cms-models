<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Strategies\ListColumn\RelationCountChildrenLink;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class ListParentHandlingTest
 *
 * Tests for simple related list parent model handling.
 *
 * @group integration
 * @group controllers
 */
class ListParentHandlingTest extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE     = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';
    const ROUTE_ALT_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testcomment';

    protected $customModelConfiguration = [
        'it_shows_a_list_of_models_in_context_of_an_active_list_parent' => [
            'test-post' => [
                'list' => [
                    'default_action' => [
                        [
                            'strategy' => \Czim\CmsModels\Support\Enums\ActionReferenceType::CHILDREN,
                            'options'  => [
                                'model'    => TestComment::class,
                                'relation' => 'post',
                            ],
                        ],
                    ],
                    'columns' => [
                        'id',
                        'comments' => [
                            'strategy' => RelationCountChildrenLink::class,
                            'options'  => [ 'relation' => 'post' ],
                        ],
                    ],
                ],
            ],
            'test-comment' => [
                'list' => [
                    'parents' => [
                        [ 'relation' => 'post' ],
                    ],
                ],
            ],
        ],
    ];


    /**
     * @test
     */
    function it_shows_a_list_of_models_in_context_of_an_active_list_parent()
    {
        // Check for child relation link in parent listing
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        $column = $this->crawler()->filter('tr.records-row[data-id=3] td.column a')->first();
        $link   = $column->attr('href');

        static::assertCount(1, $column, 'Child link for list parent not found');
        static::assertEquals(
            'http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testcomment?parent=post:3',
            $link
        );

        // Check for child list in context for parent
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index', ['parent' => 'post:3'])->seeStatusCode(200);

        // Check for contextual links
        $breadcrumbs = $this->crawler()->filter('ol.breadcrumb li');
        static::assertCount(3, $breadcrumbs, 'There should be 3 breadcrumbs');
        static::assertEquals(
            'http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testpost',
            $breadcrumbs->eq(1)->filter('a')->first()->attr('href'),
            'Breadcrumb with link to parent model not found'
        );
        static::assertGreaterThanOrEqual(
            1,
            $this->crawler()
                ->filter('a[href="http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testcomment?parents="]')
                ->count(),
            'Link to remove list context not found'
        );

        // Check if session memory works
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index')->seeStatusCode(200);
        static::assertCount(3, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 3 breadcrumbs');

        // Check if home=1 removes the parent context
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index', ['home' => '1'])->seeStatusCode(200);
        static::assertCount(2, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 2 breadcrumb');
    }

}
