<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Strategies\ListColumn\RelationCountChildrenLink;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Models\TestSeo;
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
    const ROUTE_BASE       = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';
    const ROUTE_ALT_BASE   = 'cms::models.model.czim-cmsmodels-test-helpers-models-testcomment';
    const ROUTE_MORPH_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testseo';

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
        'it_shows_a_list_of_models_in_context_of_an_active_list_parent_for_a_morph_relation' => [
            'test-post' => [
                'list' => [
                    'columns' => [
                        'id',
                        'seo' => [
                            'strategy' => RelationCountChildrenLink::class,
                            'options'  => [ 'relation' => 'seoable' ],
                        ],
                    ],
                ],
            ],
            'test-seo' => [
                'list' => [
                    'parents' => [
                        [ 'relation' => 'seoable' ],
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
        $this->visitRoute(static::ROUTE_BASE . '.index')->assertStatus(200);

        $column = $this->crawler()->filter('tr.records-row[data-id=3] td.column a')->first();
        $link   = $column->attr('href');

        static::assertCount(1, $column, 'Child link for list parent not found');
        static::assertEquals(
            'http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testcomment?parent=post:3',
            $link
        );

        // Check for child list in context for parent
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index', ['parent' => 'post:3'])->assertStatus(200);

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
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index')->assertStatus(200);
        static::assertCount(3, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 3 breadcrumbs');

        // Check if home=1 removes the parent context
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index', ['home' => '1'])->assertStatus(200);
        static::assertCount(2, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 2 breadcrumb');

        // Check if list parents can be set as a whole chain (with one element)
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index', ['parents' => 'post:3'])->assertStatus(200);
        static::assertCount(3, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 3 breadcrumbs');

        // Check if setting list parents chain to all (or empty) removes the context
        $this->visitRoute(static::ROUTE_ALT_BASE . '.index', ['parents' => 'all'])->assertStatus(200);
        static::assertCount(2, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 2 breadcrumb');
    }

    /**
     * @test
     * depends it_shows_a_list_of_models_in_context_of_an_active_list_parent
     */
    function it_shows_a_list_of_models_in_context_of_an_active_list_parent_for_a_morph_relation()
    {
        // Set up a seo connected to post #3
        TestSeo::forceCreate([
            'seoable_id'   => 3,
            'seoable_type' => TestPost::class,
            'slug'         => 'testing-slug',
        ]);

        // Check for child relation link in parent listing
        $this->visitRoute(static::ROUTE_BASE . '.index')->assertStatus(200);

        $column = $this->crawler()->filter('tr.records-row[data-id=3] td.column a')->first();
        $link   = $column->attr('href');

        static::assertCount(1, $column, 'Child link for list parent not found');
        static::assertEquals(
            'http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testseo?parent=seoable:Czim\CmsModels\Test\Helpers\Models\TestPost:3',
            $link
        );

        // Check for child list in context for parent
        $this->visitRoute(static::ROUTE_MORPH_BASE . '.index', [
            'parent' => 'seoable:Czim\CmsModels\Test\Helpers\Models\TestPost:3'
        ])->assertStatus(200);

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
                ->filter('a[href="http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testseo?parents="]')
                ->count(),
            'Link to remove list context not found'
        );

        // Check if session memory works
        $this->visitRoute(static::ROUTE_MORPH_BASE . '.index')->assertStatus(200);
        static::assertCount(3, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 3 breadcrumbs');

        // Check if home=1 removes the parent context
        $this->visitRoute(static::ROUTE_MORPH_BASE . '.index', ['home' => '1'])->assertStatus(200);
        static::assertCount(2, $this->crawler()->filter('ol.breadcrumb li'), 'There should be 2 breadcrumb');

    }

}
