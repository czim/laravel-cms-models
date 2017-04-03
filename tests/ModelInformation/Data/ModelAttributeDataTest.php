<?php
namespace Czim\CmsModels\Test\ModelInformation\Data;

use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelAttributeDataTest
 *
 * @group modelinformation-data
 */
class ModelAttributeDataTest extends TestCase
{

    /**
     * @test
     */
    function it_merges_nonempty_attributes()
    {
        $data = new ModelAttributeData;

        $data->name = 'test';
        $data->cast = 'int';

        $with = new ModelAttributeData;

        $with->name = '';
        $data->cast = 'string';

        $data->merge($with);

        static::assertEquals('test', $data->name);
        static::assertEquals('string', $data->cast);
    }

    /**
     * @test
     */
    function it_merges_data_for_translation_model_handling()
    {
        $data = new ModelAttributeData;

        $data->name = 'test';
        $data->cast = 'int';

        $with = new ModelAttributeData;

        $with->name = '';
        $data->cast = 'string';

        $data->mergeTranslation($with);

        static::assertEquals('test', $data->name);
        static::assertEquals('string', $data->cast);
    }
    
    /**
     * @test
     */
    function it_returns_whether_it_is_numeric_based_on_cast()
    {
        $data = new ModelAttributeData;

        $data->cast = AttributeCast::BOOLEAN;

        static::assertFalse($data->isNumeric());

        $data->cast = AttributeCast::INTEGER;

        static::assertTrue($data->isNumeric());

        $data->cast = AttributeCast::FLOAT;

        static::assertTrue($data->isNumeric());

        $data->cast = AttributeCast::DATE;

        static::assertFalse($data->isNumeric());
    }
    
}
