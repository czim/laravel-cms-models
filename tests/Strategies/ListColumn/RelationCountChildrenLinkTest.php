<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListColumnData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Modules\ModelModule;
use Czim\CmsModels\Strategies\ListColumn\RelationCountChildrenLink;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestAuthor;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Models\TestSeo;
use DB;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\View\View;
use Mockery;

/**
 * Class RelationCountChildrenLinkTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class RelationCountChildrenLinkTest extends AbstractPostCommentSeededTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->setUpGlobalContext();
    }

    /**
     * @test
     */
    function it_renders_a_relation_count_value_with_link()
    {
        $this->setUpSpecificContext();

        $strategy = new RelationCountChildrenLink;

        $strategy->setListInformation(new ModelListColumnData([
            'options' => [
                'relation' => 'post',
            ],
        ]));

        $model = TestPost::first();

        $view = $strategy->render($model, 'comments');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<a href="http://localhost/testing/test-comment\?parent=post:1">\s*<span class="relation-count">\s*'
            . 'list of comments \(1\)'
            . '\s*</span>\s*</a>#i',
            $view->render()
        );
    }

    /**
     * @test
     */
    function it_renders_a_relation_count_value_with_a_link_for_a_morph_to_relation()
    {
        $this->setUpSpecificContextForMorph();

        $strategy = new RelationCountChildrenLink;

        $strategy->setListInformation(new ModelListColumnData([
            'options' => [
                'relation' => 'seoable',
            ],
        ]));

        /** @var TestPost $model */
        $model = TestPost::first();

        // Make sure the model has a related seo
        $model->seo()->save(new TestSeo([ 'slug' => 'testing-post' ]));

        $view = $strategy->render($model, 'seo');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<a href="'
            . preg_quote('http://localhost/testing/test-seo?parent=seoable:Czim\\CmsModels\\Test\\Helpers\\Models\\TestPost:1')
            . '">\s*<span class="relation-count">\s*'
            . 'list of seos \(1\)'
            . '\s*</span>\s*</a>#i',
            $view->render()
        );
    }

    /**
     * @test
     */
    function it_renders_a_relation_count_value_with_a_link_for_a_morph_to_relation_with_a_morph_map()
    {
        $this->setUpSpecificContextForMorph(true);

        $strategy = new RelationCountChildrenLink;

        $strategy->setListInformation(new ModelListColumnData([
            'options' => [
                'relation' => 'seoable',
            ],
        ]));

        /** @var TestPost $model */
        $model = TestPost::first();

        // Make sure the model has a related seo
        $model->seo()->save(new TestSeo([ 'slug' => 'testing-post' ]));

        $view = $strategy->render($model, 'seo');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<a href="'
            . preg_quote('http://localhost/testing/test-seo?parent=seoable:testpost:1')
            . '">\s*<span class="relation-count">\s*'
            . 'list of seos \(1\)'
            . '\s*</span>\s*</a>#i',
            $view->render()
        );
    }

    /**
     * @test
     */
    function it_renders_a_relation_count_value_without_a_link_if_user_has_no_permission_to_see_the_child()
    {
        $this->setUpSpecificContext(false);

        $strategy = new RelationCountChildrenLink;

        $strategy->setListInformation(new ModelListColumnData([
            'options' => [
                'relation' => 'post',
            ],
        ]));

        $model = TestPost::first();

        $view = $strategy->render($model, 'comments');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<span class="relation-count">\s*1 comments\s*</span>#',
            $view->render()
        );
    }

    /**
     * @test
     */
    function it_renders_a_relation_count_value_without_a_link_as_fallback()
    {
        $strategy = new RelationCountChildrenLink;

        $strategy->setListInformation(new ModelListColumnData([
            'options' => [
                'relation' => 'post',
            ],
        ]));

        $model = TestPost::first();

        $view = $strategy->render($model, 'comments');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<span class="relation-count">\s*1 models\s*</span>#',
            $view->render()
        );
    }

    /**
     * @test
     */
    function it_renders_an_empty_relation_with_a_special_tag()
    {
        $strategy = new RelationCountChildrenLink;

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
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_list_column_data_is_not_set()
    {
        $strategy = new RelationCountChildrenLink;

        $model = TestPost::first();

        $strategy->render($model, 'comments');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_list_column_data_option_for_relation_is_not_set()
    {
        $strategy = new RelationCountChildrenLink;

        $strategy->setListInformation(new ModelListColumnData([
            'options' => [
                'relation' => null,
            ],
        ]));

        $model = TestPost::first();

        $strategy->render($model, 'comments');
    }
    
    /**
     * @test
     */
    function it_returns_null_for_style()
    {
        $strategy = new RelationCountChildrenLink;

        static::assertNull($strategy->style(new TestPost, 'comments'));
    }


    /**
     * Performs setup of global context for strategy.
     */
    protected function setUpGlobalContext()
    {
        /** @var CoreInterface|Mockery\Mock $mockCore */
        $mockCore = Mockery::mock(CoreInterface::class);
        $mockCore->shouldReceive('config')->andReturnUsing(function () { return func_get_arg(1); });
        $mockCore->shouldReceive('prefixRoute')->andReturnUsing(function () { return func_get_arg(0); });

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn(null);
        $mockRepository->shouldReceive('getByModelClass')->andReturn(null);

        /** @var ModuleManagerInterface|Mockery\Mock $mockModules */
        $mockModules = Mockery::mock(ModuleManagerInterface::class);
        $mockModules->shouldReceive('getByAssociatedClass')->andReturn(null);

        $this->app->instance(Component::CORE, $mockCore);
        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
        $this->app->instance(ModuleManagerInterface::class, $mockModules);

        // Prepare translation for link
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = $this->app['translator'];
        $translator->addLines(
            [
                'models.list-parents.models'             => 'models',
                'models.list-parents.children-list-link' => 'list of :children (:count)',
            ],
            $this->app->getLocale(),
            '*'
        );
    }

    /**
     * Performs standard (successful) list parent link handling context.
     *
     * @param bool $authorized
     */
    protected function setUpSpecificContext($authorized = true)
    {
        $commentInfo = new ModelInformation([
            'verbose_name'        => 'comment',
            'verbose_name_plural' => 'comments',
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn($commentInfo);
        $mockRepository->shouldReceive('getByModelClass')->andReturn($commentInfo);

        /** @var ModelModule|Mockery\Mock $mockModule */
        $mockModule = Mockery::mock(ModelModule::class);

        /** @var ModuleManagerInterface|Mockery\Mock $mockModules */
        $mockModules = Mockery::mock(ModuleManagerInterface::class);
        $mockModules->shouldReceive('getByAssociatedClass')->with(TestComment::class)->andReturn($mockModule);

        /** @var RouteHelperInterface|Mockery\Mock $mockRouteHelper */
        $mockRouteHelper = Mockery::mock(RouteHelperInterface::class);
        $mockRouteHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('models.test-comments.');
        $mockRouteHelper->shouldReceive('getRouteNameForModelClass')
            ->with(TestComment::class, true)->andReturn('testing.test-comment');

        /** @var ModuleHelperInterface|Mockery\Mock $mockModuleHelper */
        $mockModuleHelper = Mockery::mock(ModuleHelperInterface::class);
        $mockModuleHelper->shouldReceive('modelSlug')->andReturn('test-comments');

        /** @var ModuleHelperInterface|Mockery\Mock $mockAuth */
        $mockAuth = Mockery::mock(AuthenticatorInterface::class);
        $mockAuth->shouldReceive('can')->with('models.test-comments.show')->andReturn($authorized);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
        $this->app->instance(ModuleManagerInterface::class, $mockModules);
        $this->app->instance(RouteHelperInterface::class, $mockRouteHelper);
        $this->app->instance(ModuleHelperInterface::class, $mockModuleHelper);
        $this->app->instance(Component::AUTH, $mockAuth);

        // Prepare a testing route
        $this->app['router']->get(
            'testing/test-comment',
            ['as' => 'testing.test-comment.index', 'uses' => 'IrrelevantController@index']
        );
    }

    /**
     * Performs standard (successful) list parent link handling context for a morph relation.
     *
     * @param bool $morphMap
     */
    protected function setUpSpecificContextForMorph($morphMap = false)
    {
        $seoInfo = new ModelInformation([
            'verbose_name'        => 'seo',
            'verbose_name_plural' => 'seos',
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn($seoInfo);
        $mockRepository->shouldReceive('getByModelClass')->andReturn($seoInfo);

        /** @var ModelModule|Mockery\Mock $mockModule */
        $mockModule = Mockery::mock(ModelModule::class);

        /** @var ModuleManagerInterface|Mockery\Mock $mockModules */
        $mockModules = Mockery::mock(ModuleManagerInterface::class);
        $mockModules->shouldReceive('getByAssociatedClass')->with(TestSeo::class)->andReturn($mockModule);

        /** @var RouteHelperInterface|Mockery\Mock $mockRouteHelper */
        $mockRouteHelper = Mockery::mock(RouteHelperInterface::class);
        $mockRouteHelper->shouldReceive('getPermissionPrefixForModelSlug')->andReturn('models.test-seos.');
        $mockRouteHelper->shouldReceive('getRouteNameForModelClass')
            ->with(TestSeo::class, true)->andReturn('testing.test-seo');

        /** @var ModuleHelperInterface|Mockery\Mock $mockModuleHelper */
        $mockModuleHelper = Mockery::mock(ModuleHelperInterface::class);
        $mockModuleHelper->shouldReceive('modelSlug')->andReturn('test-seos');

        /** @var ModuleHelperInterface|Mockery\Mock $mockAuth */
        $mockAuth = Mockery::mock(AuthenticatorInterface::class);
        $mockAuth->shouldReceive('can')->with('models.test-seos.show')->andReturn(true);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
        $this->app->instance(ModuleManagerInterface::class, $mockModules);
        $this->app->instance(RouteHelperInterface::class, $mockRouteHelper);
        $this->app->instance(ModuleHelperInterface::class, $mockModuleHelper);
        $this->app->instance(Component::AUTH, $mockAuth);

        // Prepare a testing route
        $this->app['router']->get(
            'testing/test-seo',
            ['as' => 'testing.test-seo.index', 'uses' => 'IrrelevantController@index']
        );

        if ($morphMap) {
            Relation::morphMap([
                'testpost' => TestPost::class,
            ]);
        }
    }

}
