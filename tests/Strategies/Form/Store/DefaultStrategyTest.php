<?php
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleMergerInterface;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeValidationResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationValidationResolver;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\Form\Store\DefaultStrategy;
use Czim\CmsModels\Support\Validation\ValidationRuleMerger;
use Czim\CmsModels\Test\DatabaseTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Support\Facades\Schema;
use Mockery;

/**
 * Class DefaultStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class DefaultStrategyTest extends DatabaseTestCase
{

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->app->bind(ValidationRuleMergerInterface::class, ValidationRuleMerger::class);
    }

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
        $model->type = 'testing';

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals('testing', $strategy->retrieve($model, 'type'));
    }

    /**
     * @test
     */
    function it_retrieves_a_translated_value()
    {
        $model = $this->createTranslatedTestPost();

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['translated']);

        static::assertEquals(
            ['nl' => 'Nederlandse titel', 'en' => 'Some Basic Title'],
            $strategy->retrieve($model, 'title')
        );
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model()
    {
        $model = new TestPost;
        $model->type = 'testing';

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'type', 'new');

        static::assertEquals('new', $model->type);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_normalizing_to_null_for_nullable_field()
    {
        $model = new TestPost;
        $model->type = 'old';

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['nullable']);

        $strategy->store($model, 'type', '');

        static::assertNull($model->type);
    }
    
    /**
     * @test
     */
    function it_stores_to_a_method_if_it_exists_on_the_model()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'testSetValue', 'testing');

        static::assertEquals('testing', $model->test);
    }

    /**
     * @test
     */
    function it_stores_a_translated_value_on_a_model()
    {
        $model = $this->createTranslatedTestPost();

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['translated']);

        $strategy->store($model, 'title', ['nl' => 'New Dutch', 'en' => 'New English']);

        static::assertEquals('New Dutch', $model->getTranslation('nl')->title);
        static::assertEquals('New English', $model->getTranslation('en')->title);
    }

    /**
     * @test
     */
    function it_does_not_use_store_after()
    {
        $model = new TestPost;
        $model->type = 'testing';

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);

        $strategy->storeAfter($model, 'type', 'changed');

        static::assertEquals('testing', $model->type);
    }

    /**
     * @test
     */
    function it_does_not_use_store_after_for_translated_values()
    {
        $model = $this->createTranslatedTestPost();

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['translated']);

        $strategy->storeAfter($model, 'title', ['nl' => 'New Dutch', 'en' => 'New English']);

        static::assertNotEquals('New Dutch', $model->getTranslation('nl')->title);
        static::assertNotEquals('New English', $model->getTranslation('en')->title);
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_data_format_to_store_is_not_an_array_for_translated_value()
    {
        $model = $this->createTranslatedTestPost();

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['translated']);

        $strategy->store($model, 'title', 'not an array');
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_data_format_to_store_after_is_not_an_array_for_translated_value()
    {
        $model = $this->createTranslatedTestPost();

        $data = new ModelFormFieldData;

        $strategy = new DefaultStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['translated']);

        $strategy->storeAfter($model, 'title', 'not an array');
    }
    
    
    // ------------------------------------------------------------------------------
    //      List Parent Key
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_unmodified_key_as_value_for_list_parent()
    {
        $strategy = new DefaultStrategy;

        static::assertEquals('testing-key', $strategy->valueForListParentKey('testing-key'));
    }
    
    
    // ------------------------------------------------------------------------------
    //      Validation Rules      
    // ------------------------------------------------------------------------------
    
    /**
     * @test
     */
    function it_returns_no_validation_rules()
    {
        $strategy = new DefaultStrategy;

        static::assertFalse($strategy->validationRules(new ModelInformation, false));
    }

    /**
     * @return TestPost
     */
    protected function createTranslatedTestPost()
    {
        $post = TestPost::create([
            'title'       => 'Some Basic Title',
            'body'        => 'Lorem ipsum dolor sit amet.',
            'type'        => 'notice',
            'checked'     => true,
            'description' => 'the best possible post for testing',
        ]);

        $translation = $post->getNewTranslation('nl');
        $translation->title = 'Nederlandse titel';
        $translation->body = 'Nederlandse algemene tekst';
        $translation->test_post_id = $post->id;
        $translation->save();

        return $post;
    }

}
