<?php
namespace Czim\CmsModels\Test;

use Czim\CmsModels\Test\Helpers\Models\TestAuthor;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Support\Facades\Schema;

abstract class AbstractPostCommentSeededTestCase extends DatabaseTestCase
{

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
        $this->seedAuthors()
             ->seedPosts()
             ->seedComments();
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
            'title' => 'Some Basic Title',
            'body'  => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit. Cras nec erat a turpis iaculis viverra sed in dolor. Morbi nec magna eleifend, condimentum metus in, mollis orci. Aliquam bibendum est in velit semper lacinia. In ornare maximus odio eu ultrices. Nullam pulvinar nisi tempus dictum vestibulum. Morbi et felis metus. Mauris vestibulum, orci non venenatis faucibus, libero sem ultrices tellus, a faucibus dui tellus ut tellus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras ac est vitae est sodales sollicitudin sed sit amet orci. Integer porttitor faucibus libero, vitae rhoncus enim faucibus convallis. Phasellus quis orci sed odio fringilla congue nec eu elit. Mauris turpis lacus, rutrum quis turpis at, rutrum dapibus dolor. Proin placerat turpis sed lorem ultrices, vitae mattis tortor ornare.',
            'type' => 'notice',
            'checked' => true,
            'description' => 'the best possible post for testing',
        ]);
        $post->author()->associate(TestAuthor::first());
        $post->save();

        $translation = $post->getNewTranslation('nl');
        $translation->title = 'Nederlandse titel';
        $translation->body = 'Nederlandse algemene tekst';
        $translation->test_post_id = $post->id;
        $translation->save();


        $post = new TestPost([
            'title' => 'Elaborate Alternative Title',
            'body'  => 'Donec nec metus urna. Tosti pancake frying pan tortellini Fusce ex massa, commodo ut rhoncus eu, iaculis sed quam. Nam eget magna quis arcu consectetur pellentesque. In dapibus massa vel enim pharetra, tristique malesuada dolor eleifend. Suspendisse eu nisl in sem vulputate aliquam. In eleifend leo eget neque mattis, vitae auctor odio consectetur. Donec metus enim, pellentesque semper scelerisque sit amet, maximus congue neque. Proin lobortis magna pretium egestas lacinia. Suspendisse consequat quis libero vitae tempor. Pellentesque ut semper orci, vitae condimentum lorem. Praesent in mollis nunc, vitae imperdiet nibh. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'type' => 'news',
            'checked' => false,
            'description' => 'some alternative testing post',
        ]);
        $post->author()->associate(TestAuthor::first());
        $post->save();

        $post = new TestPost([
            'title' => 'Surprising Testing Title',
            'body'  => 'Aliquam pancake batter frying pan ut mauris eros. Phasellus iaculis velit nec purus rutrum eleifend. Pellentesque fringilla vulputate varius. Curabitur dignissim luctus ante, in varius est maximus quis. Donec porttitor ultricies nunc, sit amet vehicula magna viverra sit amet. Maecenas vehicula ligula nec lacus sollicitudin sollicitudin. Nullam aliquet nunc nunc, viverra porttitor urna accumsan sit amet. Aliquam vel dolor quis arcu mollis auctor. Nunc auctor pulvinar erat, ut dictum mi porta nec. Aenean id iaculis nisi.',
            'type' => 'warning',
            'checked' => true,
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

        $translation = $comment->getNewTranslation('nl');
        $translation->title = 'Nederlands commentaar';
        $translation->body = 'Nederlandse algemene tekst in commentaar';
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

}
