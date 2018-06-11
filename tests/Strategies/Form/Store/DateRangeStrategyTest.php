<?php
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Carbon\Carbon;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\Form\Store\DateRangeStrategy;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class DateRangeStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class DateRangeStrategyTest extends AbstractFormStoreStrategyTest
{

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model()
    {
        $model = new TestPost;
        $model->date_from = Carbon::createFromDate(2017, 1, 1);
        $model->date_to   = Carbon::createFromDate(2017, 3, 20);

        $data = new ModelFormFieldData([
            'options' => [
                'from' => 'date_from',
                'to'   => 'date_to',
            ],
        ]);

        $strategy = new DateRangeStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'from' => $model->date_from,
                'to'   => $model->date_to,
            ],
            $strategy->retrieve($model, 'date_from')
        );
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_date_from_option_is_not_set()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData([
            'options' => [
                'to' => 'date_to',
            ],
        ]);

        $strategy = new DateRangeStrategy;
        $strategy->setFormFieldData($data);

        $strategy->retrieve($model, 'date_from');
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_date_to_option_is_not_set()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData([
            'options' => [
                'from' => 'date_from',
            ],
        ]);

        $strategy = new DateRangeStrategy;
        $strategy->setFormFieldData($data);

        $strategy->retrieve($model, 'date_from');
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData([
            'options' => [
                'from' => 'date_from',
                'to'   => 'date_to',
            ],
        ]);

        $strategy = new DateRangeStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'date_from', [
            'from' => '2017-01-01',
            'to'   => '2017-03-20',
        ]);

        static::assertEquals('2017-01-01 00:00:00', $model->date_from);
        static::assertEquals('2017-03-20 00:00:00', $model->date_to);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_with_seconds_if_omitted()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData([
            'options' => [
                'from' => 'date_from',
                'to'   => 'date_to',
            ],
        ]);

        $strategy = new DateRangeStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'date_from', [
            'from' => '2017-01-01 00:00',
            'to'   => '2017-03-20 00:00',
        ]);

        static::assertEquals('2017-01-01 00:00:00', $model->date_from);
        static::assertEquals('2017-03-20 00:00:00', $model->date_to);
    }

    /**
     * @test
     */
    function it_returns_validation_rules_for_date_if_no_format_is_set()
    {
        $data = new ModelFormFieldData([
            'key' => 'date_range',
            'options' => [
                'from'   => 'date_from',
                'to'     => 'date_to',
            ],
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'date_range' => [],
                ],
            ],
        ]);

        $strategy = new DateRangeStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'date_range.from' => ['date'],
                'date_range.to'   => ['date'],
            ],
            $strategy->validationRules($info, false)
        );
    }

    /**
     * @test
     */
    function it_returns_validation_rules_for_format()
    {
        $data = new ModelFormFieldData([
            'key' => 'date_range',
            'options' => [
                'from'   => 'date_from',
                'to'     => 'date_to',
                'format' => 'd-m-Y H:i',
            ],
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'date_range' => [],
                ],
            ],
        ]);

        $strategy = new DateRangeStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'date_range.from' => ['date_format:d-m-Y H:i'],
                'date_range.to'   => ['date_format:d-m-Y H:i'],
            ],
            $strategy->validationRules($info, false)
        );
    }

}
