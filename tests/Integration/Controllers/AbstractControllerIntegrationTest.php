<?php
namespace Czim\CmsModels\Test\Integration\Controllers;

use Czim\CmsCore\Core\BasicNotifier;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Modules\ModelModuleGenerator;
use Czim\CmsModels\Providers\CmsModelsServiceProvider;
use Czim\CmsModels\Test\CmsBootTestCase;
use Czim\CmsModels\Test\Helpers\Core\MockApiBootChecker;
use Czim\CmsModels\Test\Helpers\Core\MockWebBootChecker;
use Czim\CmsModels\Test\Helpers\Http\Middleware\NullMiddleware;
use Czim\CmsModels\Test\Helpers\Models\TestAuthor;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class AbstractControllerIntegrationTest extends CmsBootTestCase
{
    use DatabaseTransactions;

    /**
     * Static flag to make sure we only set up the database once.
     *
     * @var bool
     */
    protected static $setupDatabase = true;

    /**
     * Whether to mock booting the API (instead of web).
     *
     * @var bool
     */
    protected $mockBootApi = false;

    /**
     * A per-test-method, per-model custom configuration to inject as collected model information.
     *
     * Use:
     *      'it_tests_method_name' => [ 'test_post' => [ ... configuration ... ] ]
     *
     * @var array
     */
    protected $customModelConfiguration = [];


    public function setUp()
    {
        // Prepare the database before the CMS can boot. This hacky approach is required
        // because the database must have analyzable contents for model analysis.
        if (self::$setupDatabase) {
            $this->prepareDatabaseWithoutApplication();
        }

        parent::setUp();

        $this->deleteModelsCacheFile();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->prepareCustomModelConfiguration($app);

        parent::getEnvironmentSetUp($app);

        // Set up configuration for modules & models
        $app['config']->set('cms-modules.modules', [
            ModelModuleGenerator::class,
        ]);

        $app['config']->set('cms-models.models', [
            TestPost::class,
            TestComment::class,
        ]);

        $app['config']->set(
            'cms-models.collector.source.dir',
            realpath('tests/Helpers/ModelConfiguration/Integration')
        );

        $app['config']->set(
            'cms-models.collector.source.models-dir',
            realpath('tests/Helpers/Models')
        );

        $app['config']->set(
            'cms-models.collector.source.models-namespace',
            'Czim\\CmsModels\\Test\\Helpers\\Models'
        );

        // Adjust middleware to disable authorization
        $app['config']->set(
            'cms-core.middleware.load',
            array_merge(
                $app['config']->get('cms-core.middleware.load'),
                [
                    \Czim\CmsCore\Support\Enums\CmsMiddleware::AUTHENTICATED => NullMiddleware::class,
                    \Czim\CmsCore\Support\Enums\CmsMiddleware::GUEST         => NullMiddleware::class,
                    \Czim\CmsCore\Support\Enums\CmsMiddleware::PERMISSION    => NullMiddleware::class,
                ]
            )
        );

        $app['view']->addNamespace('cms', realpath(dirname(__DIR__) . '/../Helpers/resources/views'));

        $app->make(\Illuminate\Contracts\Http\Kernel::class)
            ->pushMiddleware(\Illuminate\Session\Middleware\StartSession::class);

        // Required core bindings
        $app->singleton(Component::NOTIFIER, BasicNotifier::class);

        $app->register(CmsModelsServiceProvider::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function setDatabaseConnectionConfig($app)
    {
        $this->setDatabaseConnectionConfigForMysql($app);
    }

    /**
     * @return string
     */
    protected function getTestBootCheckerBinding()
    {
        if ($this->mockBootApi) {
            return MockApiBootChecker::class;
        }

        return MockWebBootChecker::class;
    }


    // ------------------------------------------------------------------------------
    //      Custom Model Information
    // ------------------------------------------------------------------------------

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function prepareCustomModelConfiguration($app)
    {
        if ( ! array_key_exists($this->getName(), $this->customModelConfiguration)) {
            return;
        }

        foreach ($this->customModelConfiguration[ $this->getName() ] as $key => $configuration) {
            $app->instance('cms-models-test.integration.information.' . $key, $configuration);
        }
    }


    // ------------------------------------------------------------------------------
    //      Database Setup
    // ------------------------------------------------------------------------------


    protected function migrateDatabase()
    {
        // Already set up before normal application boot.
        /** @see prepareDatabaseWithoutApplication */
    }

    protected function seedDatabase()
    {
        if (self::$setupDatabase) {
            $this
                ->seedAuthors()
                ->seedPosts()
                ->seedComments();
        }
    }

    protected function seedAuthors()
    {
        TestAuthor::create([
            'name' => 'Test Testington',
        ]);

        TestAuthor::create([
            'name' => 'Tosti Tortellini Von Testering',
        ]);

        return $this;
    }

    protected function seedPosts()
    {
        $post = new TestPost([
            'title'       => 'Some Basic Title',
            'body'        => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit. Cras nec erat a turpis iaculis viverra sed in dolor.',
            'type'        => 'notice',
            'checked'     => true,
            'description' => 'the best possible post for testing',
        ]);
        $post->author()->associate(TestAuthor::first());
        $post->save();

        $translation               = $post->getNewTranslation('nl');
        $translation->title        = 'Nederlandse titel';
        $translation->body         = 'Nederlandse algemene tekst';
        $translation->test_post_id = $post->id;
        $translation->save();


        $post = new TestPost([
            'title'       => 'Elaborate Alternative Title',
            'body'        => 'Donec nec metus urna. Tosti pancake frying pan tortellini Fusce ex massa, commodo ut rhoncus eu, iaculis sed quam.',
            'type'        => 'news',
            'checked'     => false,
            'description' => 'some alternative testing post',
        ]);
        $post->author()->associate(TestAuthor::first());
        $post->save();

        $post = new TestPost([
            'title'       => 'Surprising Testing Title',
            'body'        => 'Aliquam pancake batter frying pan ut mauris eros.',
            'type'        => 'announcement',
            'checked'     => true,
            'description' => 'something else',
        ]);
        $post->author()->associate(TestAuthor::skip(1)->first());
        $post->save();

        return $this;
    }

    protected function seedComments()
    {
        $comment = new TestComment([
            'title'       => 'Comment Title A',
            'body'        => 'Lorem ipsum dolor sit amet.',
            'description' => 'comment one',
        ]);
        $comment->author()->associate(TestAuthor::skip(1)->first());
        TestPost::find(1)->comments()->save($comment);

        $translation                  = $comment->getNewTranslation('nl');
        $translation->title           = 'Nederlands commentaar';
        $translation->body            = 'Nederlandse algemene tekst in commentaar';
        $translation->test_comment_id = $comment->id;
        $translation->save();


        $comment = new TestComment([
            'title'       => 'Comment Title B',
            'body'        => 'Phasellus iaculis velit nec purus rutrum eleifend.',
            'description' => 'comment two',
        ]);
        $comment->author()->associate(TestAuthor::skip(1)->first());
        TestPost::find(2)->comments()->save($comment);


        $comment = new TestComment([
            'title'       => 'Comment Title C',
            'body'        => 'Nam eget magna quis arcu consectetur pellentesque.',
            'description' => 'comment three',
        ]);
        $comment->author()->associate(TestAuthor::first());
        TestPost::find(3)->comments()->save($comment);

        return $this;
    }

    protected function prepareDatabaseWithoutApplication()
    {
        $capsule = new Capsule;

        $capsule->setAsGlobal();

        $capsule->addConnection($this->getDatabaseConfigForMysql());

        if ( ! Capsule::schema('default')->hasTable('test_genres')) {
            Capsule::schema('default')->create('test_genres', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->string('name', 50);
                $table->nullableTimestamps();
            });
        }

        if ( ! Capsule::schema('default')->hasTable('test_authors')) {
            Capsule::schema('default')->create('test_authors', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->string('name', 255);
                $table->enum('gender', ['m', 'f'])->default('f');
                $table->string('image_file_name')->nullable();
                $table->integer('image_file_size')->nullable();
                $table->string('image_content_type')->nullable();
                $table->timestamp('image_updated_at')->nullable();
                $table->nullableTimestamps();
            });
        }

        if ( ! Capsule::schema('default')->hasTable('test_posts')) {
            Capsule::schema('default')->create('test_posts', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->integer('test_author_id')->nullable()->unsigned();
                $table->integer('test_genre_id')->nullable()->unsigned();
                $table->string('description', 255)->nullable();
                $table->enum('type', ['announcement', 'news', 'notice', 'periodical'])->default('news');
                $table->boolean('checked')->default(false);
                $table->nullableTimestamps();
            });
        }

        if ( ! Capsule::schema('default')->hasTable('test_post_translations')) {
            Capsule::schema('default')->create('test_post_translations', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->char('locale', 12)->nullable();
                $table->integer('test_post_id')->nullable()->unsigned();
                $table->string('title', 50);
                $table->text('body');
                $table->nullableTimestamps();
            });
        }

        if ( ! Capsule::schema('default')->hasTable('test_comments')) {
            Capsule::schema('default')->create('test_comments', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->integer('test_post_id')->unsigned();
                $table->integer('test_author_id')->nullable()->unsigned();
                $table->string('description', 255)->nullable();
                $table->nullableTimestamps();
            });
        }

        if ( ! Capsule::schema('default')->hasTable('test_comment_translations')) {
            Capsule::schema('default')->create('test_comment_translations', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->char('locale', 12)->nullable();
                $table->integer('test_comment_id')->nullable()->unsigned();
                $table->string('title', 50)->nullable();
                $table->text('body')->nullable();
                $table->nullableTimestamps();
            });
        }

        if ( ! Capsule::schema('default')->hasTable('test_seos')) {
            Capsule::schema('default')->create('test_seos', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->integer('seoable_id')->unsigned()->nullable();
                $table->string('seoable_type', 255)->nullable();
                $table->string('slug', 255);
                $table->nullableTimestamps();
            });
        }

        // Make sure all content is cleared & auto-increment keys are reset
        Capsule::statement('SET FOREIGN_KEY_CHECKS=0');
        Capsule::table('test_genres')->truncate();
        Capsule::table('test_authors')->truncate();
        Capsule::table('test_posts')->truncate();
        Capsule::table('test_post_translations')->truncate();
        Capsule::table('test_comments')->truncate();
        Capsule::table('test_comment_translations')->truncate();
        Capsule::table('test_seos')->truncate();
        Capsule::statement('SET FOREIGN_KEY_CHECKS=1');
    }

}
