<?php
namespace Czim\CmsModels\Test;

use Illuminate\Support\Facades\Schema;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('translatable.locales', [ 'en', 'nl' ]);
        $app['config']->set('translatable.use_fallback', true);
        $app['config']->set('translatable.fallback_locale', 'en');
    }

    public function setUp()
    {
        parent::setUp();

        $this->migrateDatabase();
        $this->seedDatabase();
    }


    protected function migrateDatabase()
    {
        Schema::create('test_genres', function($table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->nullableTimestamps();
        });

        Schema::create('test_authors', function($table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->enum('gender', [ 'm', 'f' ])->default('f');
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('test_posts', function($table) {
            $table->increments('id');
            $table->integer('test_author_id')->nullable()->unsigned();
            $table->integer('test_genre_id')->nullable()->unsigned();
            $table->string('description', 255)->nullable();
            $table->enum('type', [ 'announcement', 'news', 'notice', 'periodical' ])->default('news');
            $table->boolean('checked')->default(false);
            $table->nullableTimestamps();
        });

        Schema::create('test_post_translations', function($table) {
            $table->increments('id');
            $table->char('locale', 12)->nullable();
            $table->integer('test_post_id')->nullable()->unsigned();
            $table->string('title', 50);
            $table->text('body');
            $table->nullableTimestamps();
        });

        Schema::create('test_comments', function($table) {
            $table->increments('id');
            $table->integer('test_post_id')->unsigned();
            $table->integer('test_author_id')->nullable()->unsigned();
            $table->string('description', 255)->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('test_comment_translations', function($table) {
            $table->increments('id');
            $table->char('locale', 12)->nullable();
            $table->integer('test_comment_id')->nullable()->unsigned();
            $table->string('title', 50)->nullable();
            $table->text('body')->nullable();
            $table->nullableTimestamps();
        });

        //Schema::create('test_author_test_post', function($table) {
        //    $table->increments('id');
        //    $table->integer('test_author_id')->unsigned();
        //    $table->integer('test_post_id')->unsigned();
        //});

        Schema::create('test_seos', function($table) {
            $table->increments('id');
            $table->integer('seoable_id')->unsigned()->nullable();
            $table->string('seoable_type', 255)->nullable();
            $table->string('slug', 255);
            $table->nullableTimestamps();
        });
    }

    protected function seedDatabase()
    {
    }

}
