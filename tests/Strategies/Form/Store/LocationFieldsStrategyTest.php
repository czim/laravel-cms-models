<?php
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\Form\Store\LocationFieldsStrategy;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;

/**
 * Class LocationFieldsStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class LocationFieldsStrategyTest extends TestCase
{

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model()
    {
        $model = new TestPost;
        $model->longitude = 10.3;
        $model->latitude  = 50.5;
        $model->location  = 'Some City';

        $data = new ModelFormFieldData([
            'key' => 'location',
        ]);

        $strategy = new LocationFieldsStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'latitude'  => $model->latitude,
                'longitude' => $model->longitude,
                'location'  => $model->location,
            ],
            $strategy->retrieve($model, 'location')
        );
    }

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model_with_custom_attributes()
    {
        $model = new TestPost;
        $model->test_longitude = 10.3;
        $model->test_latitude  = 50.5;
        $model->test_location  = 'Some City';

        $data = new ModelFormFieldData([
            'key'     => 'location',
            'options' => [
                'latitude_name'  => 'test_latitude',
                'longitude_name' => 'test_longitude',
                'location_name'  => 'test_location',
            ],
        ]);

        $strategy = new LocationFieldsStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'latitude'  => $model->test_latitude,
                'longitude' => $model->test_longitude,
                'location'  => $model->test_location,
            ],
            $strategy->retrieve($model, 'location')
        );
    }

    /**
     * @test
     */
    function it_ignores_values_for_columns_if_option_name_is_false()
    {
        $model = new TestPost;
        $model->test_longitude = 10.3;
        $model->test_latitude  = 50.5;
        $model->test_location  = 'Some City';

        $data = new ModelFormFieldData([
            'key'     => 'location',
            'options' => [
                'latitude_name'  => false,
                'longitude_name' => false,
                'location_name'  => false,
            ],
        ]);

        $strategy = new LocationFieldsStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'latitude'  => null,
                'longitude' => null,
                'location'  => null,
            ],
            $strategy->retrieve($model, 'location')
        );
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData;

        $strategy = new LocationFieldsStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'location', [
            'latitude'  => 40.3,
            'longitude' => 10.5,
            'location'  => 'Test Location',
        ]);

        static::assertEquals(40.3, $model->latitude);
        static::assertEquals(10.5, $model->longitude);
        static::assertEquals('Test Location', $model->location);
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_with_custom_attributes()
    {
        $model = new TestPost;

        $data = new ModelFormFieldData([
            'options' => [
                'latitude_name'  => 'test_latitude',
                'longitude_name' => 'test_longitude',
                'location_name'  => 'test_location',
            ],
        ]);

        $strategy = new LocationFieldsStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'location', [
            'latitude'  => 40.3,
            'longitude' => 10.5,
            'location'  => 'Test Location',
        ]);

        static::assertEquals(40.3, $model->test_latitude);
        static::assertEquals(10.5, $model->test_longitude);
        static::assertEquals('Test Location', $model->test_location);
    }

    /**
     * @test
     */
    function it_returns_validation_rules()
    {
        $data = new ModelFormFieldData([
            'key' => 'location',
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'location' => [],
                ],
            ],
        ]);

        $strategy = new LocationFieldsStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'location.latitude'  => ['numeric'],
                'location.longitude' => ['numeric'],
                'location.text'      => ['string'],
            ],
            $strategy->validationRules($info, false)
        );
    }

    /**
     * @test
     */
    function it_returns_validation_rules_for_required_field()
    {
        $data = new ModelFormFieldData([
            'key'      => 'location',
            'required' => true,
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'location' => [],
                ],
            ],
        ]);

        $strategy = new LocationFieldsStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals(
            [
                'location.latitude'  => ['numeric', 'required'],
                'location.longitude' => ['numeric', 'required'],
                'location.text'      => ['string'],
            ],
            $strategy->validationRules($info, false)
        );
    }

}
