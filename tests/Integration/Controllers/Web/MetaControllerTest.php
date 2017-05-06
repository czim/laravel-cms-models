<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Test\Helpers\Models\TestAuthor;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Models\TestSeo;
use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class MetaControllerTest
 *
 * Tests for meta controller actions.
 *
 * @group integration
 * @group controllers
 */
class MetaControllerTest extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models-meta.';

    protected $customModelConfiguration = [
        'it_returns_meta_model_references_for_multiple_models_defined_for_morph_relation' => [
            'test-seo' => [
                'form' => [
                    'fields' => [
                        'seoable' => [
                            'options' => [
                                'models' => [
                                    TestPost::class    => [],
                                    TestComment::class => [],
                                    TestAuthor::class  => [],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];


    /**
     * @test
     */
    function it_returns_meta_model_references()
    {
        $this->post(route(static::ROUTE_BASE . 'references'), [
            'model' => TestPost::class,
            'type'  => 'form.field',
            'key'   => 'comments',
        ])
            ->seeStatusCode(200)
            ->seeJsonStructure([
                '*' => ['key', 'reference']
            ])
            ->seeJsonContains([
                'key'       => 1,
                'reference' => '#1: Comment Title A',
            ])
            ->seeJsonContains([
                'key'       => 2,
                'reference' => '#2: Comment Title B',
            ])
            ->seeJsonContains([
                'key'       => 3,
                'reference' => '#3: Comment Title C',
            ]);
    }

    /**
     * @test
     */
    function it_returns_meta_model_references_for_multiple_models_defined_for_morph_relation()
    {
        $this->post(route(static::ROUTE_BASE . 'references'), [
            'model' => TestSeo::class,
            'type'  => 'form.field',
            'key'   => 'seoable',
        ])
            ->seeStatusCode(200)
            ->seeJsonStructure([
                TestPost::class    => ['*' => ['key', 'reference']],
                TestComment::class => ['*' => ['key', 'reference']],
                TestAuthor::class  => ['*' => ['key', 'reference']],
            ])
            // Posts
            ->seeJsonContains([
                'key'       => 1,
                'reference' => '#1: Some Basic Title',
            ])
            ->seeJsonContains([
                'key'       => 2,
                'reference' => '#2: Elaborate Alternative Title',
            ])
            // Comments
            ->seeJsonContains([
                'key'       => 1,
                'reference' => '#1: Comment Title A',
            ])
            ->seeJsonContains([
                'key'       => 2,
                'reference' => '#2: Comment Title B',
            ])
            ->seeJsonContains([
                'key'       => 3,
                'reference' => '#3: Comment Title C',
            ])
            // Authors
            ->seeJsonContains([
                'key'       => 1,
                'reference' => '#1: Test Testington',
            ])
            ->seeJsonContains([
                'key'       => 2,
                'reference' => '#2: Tosti Tortellini Von Testering',
            ]);
    }

    /**
     * @test
     */
    function it_returns_meta_model_references_filtered_by_search_string()
    {
        $this->post(route(static::ROUTE_BASE . 'references'), [
            'model'  => TestPost::class,
            'type'   => 'form.field',
            'key'    => 'comments',
            'search' => 'a',
        ])
            ->seeStatusCode(200)
            ->seeJson();

        // Check that json does not contain more than it should
        $content = json_decode($this->response->content(), true);

        static::assertEquals(
            [
                [
                    'key'       => 1,
                    'reference' => '#1: Comment Title A',
                ]
            ],
            $content,
            'JSON content is incorrect'
        );
    }

    /**
     * @test
     */
    function it_returns_404_if_it_cannot_interpret_reference_data()
    {
        $this->post(route(static::ROUTE_BASE . 'references'), [
            'model' => TestPost::class,
            'type'  => 'unknown.type',
            'key'   => 'unknown',
        ])->seeStatusCode(404);
    }

    /**
     * @test
     */
    function it_returns_404_if_a_referenced_class_is_unknown_by_the_cms()
    {
        $this->post(route(static::ROUTE_BASE . 'references'), [
            'model' => static::class,
            'type'  => 'form.field',
            'key'   => 'irrelevant',
        ])->seeStatusCode(404);
    }

}
