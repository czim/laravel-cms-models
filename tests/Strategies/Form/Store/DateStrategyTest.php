<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Carbon\Carbon;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\Form\Store\DateStrategy;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class DateStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class DateStrategyTest extends AbstractFormStoreStrategyTest
{

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model()
    {
        $model = new TestPost;
        $model->date = Carbon::createFromDate(2017, 1, 1);

        $data = new ModelFormFieldData;

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            $model->date,
            $strategy->retrieve($model, 'date')
        );
    }

    /**
     * @test
     */
    function it_stores_an_empty_value_as_null()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData;

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'date', '');

        static::assertNull($model->date);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_for_unknown_display_strategy()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData;

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'date', '2017-01-01 00:00:00');

        static::assertEquals('2017-01-01 00:00:00', $model->date);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_for_datetime_display_strategy()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData([
            'display_strategy' => FormDisplayStrategy::DATEPICKER_DATETIME,
        ]);

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'date', '2017-01-01 00:00');

        static::assertEquals('2017-01-01 00:00:00', $model->date);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_for_date_display_strategy()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData([
            'display_strategy' => FormDisplayStrategy::DATEPICKER_DATE,
        ]);

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'date', '2017-01-01');

        static::assertEquals('2017-01-01 00:00:00', $model->date);
    }

    /**
     * @test
     */
    function it_returns_validation_rules_for_date_if_no_format_is_set()
    {
        $data = new ModelFormFieldData([
            'key' => 'date',
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'date' => [],
                ],
            ],
        ]);

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        /** @var ValidationRuleDataInterface[] $rules */
        $rules = $strategy->validationRules($info, false);

        static::assertCount(1, $rules);
        static::assertInstanceOf(ValidationRuleDataInterface::class, $rules[0]);
        static::assertEmpty($rules[0]->key());
        static::assertEquals(['date'], $rules[0]->rules());
    }

    /**
     * @test
     */
    function it_returns_validation_rules_for_format()
    {
        $data = new ModelFormFieldData([
            'key' => 'date',
            'options' => [
                'format' => 'd-m-Y H:i',
            ],
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'date' => [],
                ],
            ],
        ]);

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        /** @var ValidationRuleDataInterface[] $rules */
        $rules = $strategy->validationRules($info, false);

        static::assertCount(1, $rules);
        static::assertInstanceOf(ValidationRuleDataInterface::class, $rules[0]);
        static::assertEmpty($rules[0]->key());
        static::assertEquals(['date_format:d-m-Y H:i'], $rules[0]->rules());
    }

    /**
     * @test
     */
    function it_returns_validation_rules_for_time_strategy_without_set_format()
    {
        $data = new ModelFormFieldData([
            'key'              => 'date',
            'display_strategy' => FormDisplayStrategy::DATEPICKER_TIME,
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'date' => [],
                ],
            ],
        ]);

        $strategy = new DateStrategy;
        $strategy->setFormFieldData($data);

        /** @var ValidationRuleDataInterface[] $rules */
        $rules = $strategy->validationRules($info, false);

        static::assertCount(1, $rules);
        static::assertInstanceOf(ValidationRuleDataInterface::class, $rules[0]);
        static::assertEmpty($rules[0]->key());
        static::assertEquals(['date_format:H:i'], $rules[0]->rules());
    }

}
