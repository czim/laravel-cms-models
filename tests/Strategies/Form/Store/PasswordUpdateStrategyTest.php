<?php
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\Strategies\Form\Store\PasswordUpdateStrategy;
use Czim\CmsModels\Test\DatabaseTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Support\Facades\Schema;

/**
 * Class PasswordUpdateStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class PasswordUpdateStrategyTest extends DatabaseTestCase
{

    protected function migrateDatabase()
    {
        Schema::create('test_posts', function($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('test_author_id')->nullable()->unsigned();
            $table->integer('test_genre_id')->nullable()->unsigned();
            $table->string('description', 255)->nullable();
            $table->enum('type', [ 'announcement', 'news', 'notice', 'periodical' ])->default('news');
            $table->boolean('checked')->default(false);
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

    protected function seedDatabase()
    {
    }

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model()
    {
        $model = new TestPost;
        $model->password = 'hashhere';

        $data = new ModelFormFieldData;

        $strategy = new PasswordUpdateStrategy;
        $strategy->setFormFieldData($data);

        static::assertNull($strategy->retrieve($model, 'password'));
    }

    /**
     * @test
     */
    function it_retrieves_a_translated_null_value_for_every_locale_of_a_translated_attribute()
    {
        $post = TestPost::create([
            'title'       => 'english',
            'body'        => 'Lorem ipsum dolor sit amet.',
            'type'        => 'notice',
            'checked'     => true,
            'description' => 'testing',
        ]);

        $translation = $post->getNewTranslation('nl');
        $translation->title = 'dutch';
        $translation->body = 'Nederlandse algemene tekst';
        $translation->test_post_id = $post->id;
        $translation->save();

        $data = new ModelFormFieldData([
            'translated' => true,
        ]);

        $strategy = new PasswordUpdateStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['translated']);

        static::assertEquals(['en' => null, 'nl' => null], $strategy->retrieve($post, 'title'));
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData;

        $strategy = new PasswordUpdateStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'password', 'new password');

        static::assertTrue(\Hash::check('new password', $model->password));
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_for_the_indicated_method()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData;

        $strategy = new PasswordUpdateStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'testSetValue', 'new password');

        static::assertTrue(\Hash::check('new password', $model->test));
    }

}
