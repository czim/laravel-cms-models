<?php
namespace Czim\CmsModels\Test\Integration\Analyzer\Processor;

use Czim\CmsModels\Test\CmsBootTestCase;
use Illuminate\Support\Facades\Schema;

abstract class AbstractAnalyzerTestCase extends CmsBootTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }

    protected function migrateDatabase()
    {
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
    }

}
