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
            ->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['key', 'reference']
            ])
            ->assertJsonFragment([
                'key'       => 1,
                'reference' => '#1: Comment Title A',
            ])
            ->assertJsonFragment([
                'key'       => 2,
                'reference' => '#2: Comment Title B',
            ])
            ->assertJsonFragment([
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
            ->assertStatus(200)
            ->assertJsonStructure([
                TestPost::class    => ['*' => ['key', 'reference']],
                TestComment::class => ['*' => ['key', 'reference']],
                TestAuthor::class  => ['*' => ['key', 'reference']],
            ])
            // Posts
            ->assertJsonFragment([
                'key'       => 1,
                'reference' => '#1: Some Basic Title',
            ])
            ->assertJsonFragment([
                'key'       => 2,
                'reference' => '#2: Elaborate Alternative Title',
            ])
            // Comments
            ->assertJsonFragment([
                'key'       => 1,
                'reference' => '#1: Comment Title A',
            ])
            ->assertJsonFragment([
                'key'       => 2,
                'reference' => '#2: Comment Title B',
            ])
            ->assertJsonFragment([
                'key'       => 3,
                'reference' => '#3: Comment Title C',
            ])
            // Authors
            ->assertJsonFragment([
                'key'       => 1,
                'reference' => '#1: Test Testington',
            ])
            ->assertJsonFragment([
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
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'key'       => 1,
                    'reference' => '#1: Comment Title A',
                ]
            ]
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
        ])->assertStatus(404);
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
        ])->assertStatus(404);
    }

}
