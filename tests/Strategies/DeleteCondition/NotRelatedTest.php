<?php
namespace Czim\CmsModels\Test\Strategies\DeleteCondition;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\DeleteCondition\NotRelated;
use Czim\CmsModels\Test\DatabaseTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestAuthor;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Support\Facades\Schema;
use Mockery;

/**
 * Class NotRelatedTest
 *
 * @group strategies
 * @group strategies-delete-condition
 */
class NotRelatedTest extends DatabaseTestCase
{

    protected function migrateDatabase()
    {
        Schema::create('test_authors', function($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->string('name', 255);
            $table->enum('gender', [ 'm', 'f' ])->default('f');
            $table->integer('position')->unsigned()->nullable();
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('test_posts', function($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('test_author_id')->nullable()->unsigned();
            $table->integer('test_genre_id')->nullable()->unsigned();
            $table->string('description', 255)->nullable();
            $table->enum('type', [ 'announcement', 'news', 'notice', 'periodical' ])->default('news');
            $table->boolean('checked')->default(false);
            $table->integer('position')->unsigned()->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('test_post_translations', function($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->char('locale', 12)->nullable();
            $table->integer('test_post_id')->nullable()->unsigned();
            $table->string('title', 50);
            $table->text('body');
            $table->nullableTimestamps();
        });
    }

    /**
     * @test
     */
    function it_reports_related_record_undeletable()
    {
        $author = TestAuthor::create([
            'name' => 'Test',
        ]);

        $post = new TestPost([
            'en' => [
                'title' => 'Test',
                'body'  => 'Body',
            ],
            'checked' => true,
        ]);
        $post->author()->associate($author);
        $post->save();


        $info = new ModelInformation([
            'relations' => [
                'author' => [
                    'method' => 'author',
                ],
                'translations' => [
                    'method' => 'translations',
                ],
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($post)->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        $condition = new NotRelated;

        static::assertFalse($condition->check($post, []));
    }

    /**
     * @test
     */
    function it_reports_unrelated_record_deletable()
    {
        $post = new TestPost([
            'checked' => true,
        ]);
        $post->save();

        $info = new ModelInformation([
            'relations' => [
                'author' => [
                    'method' => 'author',
                ],
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($post)->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        $condition = new NotRelated;

        static::assertTrue($condition->check($post, []));
    }
    
    /**
     * @test
     */
    function it_reports_a_model_without_information_or_relations_deletable()
    {
        $model = new TestPost;

        $info = new ModelInformation;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info, null);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        $condition = new NotRelated;

        static::assertTrue($condition->check($model, []));
        static::assertTrue($condition->check($model, []));
    }

    /**
     * @test
     */
    function it_uses_parameters_to_determine_relations_if_given()
    {
        $author = TestAuthor::create([
            'name' => 'Test',
        ]);

        $post = new TestPost([
            'checked' => true,
        ]);
        $post->author()->associate($author);
        $post->save();

        $condition = new NotRelated;

        // Author should be ignored here
        static::assertTrue($condition->check($post, ['translations']));
    }

    /**
     * @test
     */
    function it_returns_a_message()
    {
        /** @var CoreInterface|Mockery\Mock $coreMock */
        $coreMock = Mockery::mock(CoreInterface::class);
        $coreMock->shouldReceive('config')->with('translation.prefix', 'cms::')->andReturn('cms::');
        $this->app->instance(Component::CORE, $coreMock);

        $condition = new NotRelated;

        $this->app['translator']->addLines(['models.delete.failure.in-use' => 'Test Message'], 'en', '*');

        static::assertEquals('Test Message', $condition->message());
    }

}
