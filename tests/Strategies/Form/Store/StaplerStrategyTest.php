<?php
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\Strategies\Form\Store\StaplerStrategy;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;

/**
 * Class StaplerStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class StaplerStrategyTest extends TestCase
{

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model()
    {
        $model = new TestPost;
        $model->test = 'test';

        $data = new ModelFormFieldData;

        $strategy = new StaplerStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals('test', $strategy->retrieve($model, 'test'));
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_if_keep_is_not_flagged()
    {
        $model = new TestPost;
        $model->test = false;

        $data = new ModelFormFieldData;

        $strategy = new StaplerStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'test', [
            'keep'   => false,
            'upload' => 'testing',
        ]);

        static::assertEquals('testing', $model->test);
    }

    /**
     * @test
     */
    function it_does_not_store_a_value_on_a_model_if_keep_is_flagged()
    {
        $model = new TestPost;
        $model->test = false;

        $data = new ModelFormFieldData;

        $strategy = new StaplerStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'test', [
            'keep'   => true,
            'upload' => 'testing',
        ]);

        static::assertFalse($model->test);
    }

}
