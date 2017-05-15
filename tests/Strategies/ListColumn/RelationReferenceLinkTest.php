<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Modules\ModelModule;
use Czim\CmsModels\Strategies\ListColumn\RelationReferenceLink;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use DB;
use Mockery;

/**
 * Class RelationReferenceLinkTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class RelationReferenceLinkTest extends AbstractPostCommentSeededTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->setUpGlobalContext();
    }

    /**
     * @test
     */
    function it_renders_a_reference_string_for_a_relation_with_link_to_edit_form_if_allowed()
    {
        $this->setUpSpecificContext();

        $strategy = new RelationReferenceLink;

        $model = TestPost::first();

        static::assertEquals(
            '<a href="http://localhost/testing/test-comment/edit?1"><span class="relation-reference">#1: Comment Title A</span></a>',
            $strategy->render($model, 'comments')
        );
    }

    /**
     * @test
     */
    function it_renders_a_reference_string_for_a_relation_with_link_to_show_page_if_edit_not_allowed()
    {
        $this->setUpSpecificContext(true, false);

        $strategy = new RelationReferenceLink;

        $model = TestPost::first();

        static::assertEquals(
            '<a href="http://localhost/testing/test-comment/show?1"><span class="relation-reference">#1: Comment Title A</span></a>',
            $strategy->render($model, 'comments')
        );
    }


    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_the_source_is_not_an_eloquent_relation()
    {
        $strategy = new RelationReferenceLink;

        /** @var TestPost $model */
        $model = TestPost::first();

        $strategy->render($model, 'testMethod');
    }

    /**
     * @test
     */
    function it_renders_an_empty_relation_with_a_special_tag()
    {
        $strategy = new RelationReferenceLink;

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
     *
     * @param bool $authorized
     * @param bool $mayEdit
     */
    protected function setUpSpecificContext($authorized = true, $mayEdit = true)
    {
        $commentInfo = new ModelInformation([
            'reference' => [
                'source' => 'title',
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn($commentInfo);

        /** @var ModelModule|Mockery\Mock $mockModule */
        $mockModule = Mockery::mock(ModelModule::class);

        /** @var ModuleManagerInterface|Mockery\Mock $mockModules */
        $mockModules = Mockery::mock(ModuleManagerInterface::class);
        $mockModules->shouldReceive('getByAssociatedClass')->with(TestComment::class)->andReturn($mockModule);

        /** @var RouteHelperInterface|Mockery\Mock $mockRouteHelper */
        $mockRouteHelper = Mockery::mock(RouteHelperInterface::class);
        $mockRouteHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('models.test-comments.');
        $mockRouteHelper->shouldReceive('getRouteSlugForModelClass')->andReturn('test-comments');
        $mockRouteHelper->shouldReceive('getRouteNameForModelClass')
            ->with(TestComment::class, true)->andReturn('testing.test-comment');

        /** @var ModuleHelperInterface|Mockery\Mock $mockModuleHelper */
        $mockModuleHelper = Mockery::mock(ModuleHelperInterface::class);
        $mockModuleHelper->shouldReceive('modelSlug')->andReturn('test-comments');

        /** @var ModuleHelperInterface|Mockery\Mock $mockAuth */
        $mockAuth = Mockery::mock(AuthenticatorInterface::class);
        $mockAuth->shouldReceive('can')->with('models.test-comments.edit')->andReturn($authorized && $mayEdit);
        $mockAuth->shouldReceive('can')->with('models.test-comments.show')->andReturn($authorized);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
        $this->app->instance(ModuleManagerInterface::class, $mockModules);
        $this->app->instance(RouteHelperInterface::class, $mockRouteHelper);
        $this->app->instance(ModuleHelperInterface::class, $mockModuleHelper);
        $this->app->instance(Component::AUTH, $mockAuth);

        // Prepare a testing route
        $this->app['router']->get(
            'testing/test-comment/show',
            ['as' => 'testing.test-comment.show', 'uses' => 'IrrelevantController@index']
        );
        $this->app['router']->get(
            'testing/test-comment/edit',
            ['as' => 'testing.test-comment.edit', 'uses' => 'IrrelevantController@index']
        );
    }

}
