<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\ListColumn\RelationReference;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Models\TestSeo;
use DB;
use Mockery;

/**
 * Class RelationReferenceTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class RelationReferenceTest extends AbstractPostCommentSeededTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->setUpGlobalContext();
    }

    /**
     * @test
     */
    function it_renders_a_reference_string_for_a_relation()
    {
        $this->setUpSpecificContext();

        $strategy = new RelationReference;

        $model = TestPost::first();

        static::assertEquals(
            '<span class="relation-reference">#1: Comment Title A</span>',
            $strategy->render($model, 'comments')
        );
    }

    /**
     * @test
     */
    function it_renders_a_relation_reference_for_a_morph_to_relation()
    {
        $this->setUpSpecificContextForMorph();

        $strategy = new RelationReference;

        /** @var TestPost $model */
        $model = TestPost::first();

        // Make sure the model has a related seo
        $model->seo()->save(new TestSeo([ 'slug' => 'testing-post' ]));

        static::assertEquals(
            '<span class="relation-reference">#1: testing-post</span>',
            $strategy->render($model, 'seo')
        );
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_the_source_is_not_an_eloquent_relation()
    {
        $strategy = new RelationReference;

        /** @var TestPost $model */
        $model = TestPost::first();

        $strategy->render($model, 'testMethod');
    }

    /**
     * @test
     */
    function it_renders_an_empty_relation_with_a_special_tag()
    {
        $strategy = new RelationReference;

        // Delete the related comments, force an empty relation
        DB::table('test_comments')->delete();

        $model = TestPost::first();

        static::assertEquals(
            '<span class="relation-reference reference-empty">&nbsp;</span>',
            $strategy->render($model, 'comments')
        );
    }


    /**
     * Performs setup of global context for strategy.
     */
    protected function setUpGlobalContext()
    {
        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn(null);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
    }

    /**
     * Performs standard (successful) reference setup.
     */
    protected function setUpSpecificContext()
    {
        $commentInfo = new ModelInformation([
            'reference' => [
                'source' => 'title',
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn($commentInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
    }

    /**
     * Performs standard (successful) reference setup for a morph relation.
     */
    protected function setUpSpecificContextForMorph()
    {
        $seoInfo = new ModelInformation([
            'reference' => [
                'source' => 'slug',
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn($seoInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
    }

}
