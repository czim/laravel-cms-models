<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Strategies\ListColumn\RelationCount;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use DB;
use Mockery;

/**
 * Class RelationCountTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class RelationCountTest extends AbstractPostCommentSeededTestCase
{

    public function setUp()
    {
        parent::setUp();

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn(null);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
    }

    /**
     * @test
     */
    function it_renders_a_relation_count_value()
    {
        $strategy = new RelationCount;

        $model = TestPost::first();

        static::assertEquals(
            '<span class="relation-count">1</span>',
            $strategy->render($model, 'comments')
        );
    }

    /**
     * @test
     */
    function it_renders_an_empty_relation_with_a_special_tag()
    {
        $strategy = new RelationCount;

        // Delete the related comments, force an empty relation
        DB::table('test_comments')->delete();

        $model = TestPost::first();

        static::assertEquals(
            '<span class="relation-count count-empty">&nbsp;</span>',
            $strategy->render($model, 'comments')
        );
    }
    
    /**
     * @test
     */
    function it_returns_right_aligned_style()
    {
        $strategy = new RelationCount;

        static::assertEquals('text-right', $strategy->style(new TestPost, 'comments'));
    }

}
