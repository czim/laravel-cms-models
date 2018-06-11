<?php
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\Strategies\Form\Store\BooleanStrategy;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class BooleanStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class BooleanStrategyTest extends AbstractFormStoreStrategyTest
{

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model()
    {
        $model = new TestPost;
        $model->checked = true;

        $data = new ModelFormFieldData;

        $strategy = new BooleanStrategy;
        $strategy->setFormFieldData($data);

        static::assertTrue($strategy->retrieve($model, 'checked'));
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model()
    {
        $model = new TestPost;
        $model->checked = false;

        $data = new ModelFormFieldData;

        $strategy = new BooleanStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'checked', true);

        static::assertTrue($model->checked);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_normalizing_it_to_bool()
    {
        $model = new TestPost;
        $model->checked = false;

        $data = new ModelFormFieldData;

        $strategy = new BooleanStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'checked', 'yes it is true');

        static::assertTrue($model->checked);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_defaulting_to_false_if_not_nullable()
    {
        $model = new TestPost;
        $model->checked = true;

        $data = new ModelFormFieldData;

        $strategy = new BooleanStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'checked', null);

        static::assertFalse($model->checked);

        // Check when nullable
        $strategy = new BooleanStrategy;
        $strategy->setFormFieldData($data);
        $strategy->setParameters(['nullable']);

        $strategy->store($model, 'checked', null);

        static::assertNull($model->checked);
    }

    /**
     * @test
     */
    function it_does_not_use_store_after()
    {
        $model = new TestPost;
        $model->checked = false;

        $data = new ModelFormFieldData;

        $strategy = new BooleanStrategy;
        $strategy->setFormFieldData($data);

        $strategy->storeAfter($model, 'checked', true);

        static::assertFalse($model->checked);
    }

}
