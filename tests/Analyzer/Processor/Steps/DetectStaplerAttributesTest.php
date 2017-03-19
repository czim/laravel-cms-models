<?php
namespace Czim\CmsModels\Test\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps\DetectStaplerAttributes;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestActivatable;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestStapler;

/**
 * Class DetectStaplerAttributesTest
 *
 * @group analysis
 */
class DetectStaplerAttributesTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_detects_stapler_attributes()
    {
        // Setup
        $model    = new TestStapler;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        $info->attributes = [
            'file_file_name' => new ModelAttributeData([
                'name'     => 'file_file_name',
                'cast'     => AttributeCast::STRING,
                'nullable' => true,
            ]),
            'file_file_size' => new ModelAttributeData([
                'name'     => 'file_file_size',
                'cast'     => AttributeCast::INTEGER,
                'nullable' => true,
            ]),
            'file_content_type' => new ModelAttributeData([
                'name'     => 'file_content_type',
                'cast'     => AttributeCast::STRING,
                'nullable' => true,
            ]),
            'file_updated_at' => new ModelAttributeData([
                'name'     => 'file_updated_at',
                'cast'     => AttributeCast::DATE,
                'nullable' => true,
            ]),
            'image_file_name' => new ModelAttributeData([
                'name'     => 'image_file_name',
                'cast'     => AttributeCast::STRING,
                'nullable' => true,
            ]),
            'image_file_size' => new ModelAttributeData([
                'name'     => 'image_file_size',
                'cast'     => AttributeCast::INTEGER,
                'nullable' => true,
            ]),
            'image_content_type' => new ModelAttributeData([
                'name'     => 'image_content_type',
                'cast'     => AttributeCast::STRING,
                'nullable' => true,
            ]),
            'image_updated_at' => new ModelAttributeData([
                'name'     => 'image_updated_at',
                'cast'     => AttributeCast::DATE,
                'nullable' => true,
            ]),
        ];

        // Test
        $step = new DetectStaplerAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertArrayHasKey('file', $info['attributes'], 'Stapler attribute not added');
        static::assertArrayHasKey('image', $info['attributes'], 'Stapler attribute not added');

        static::assertEquals(
            [
                'file', 'file_file_name', 'file_file_size', 'file_content_type', 'file_updated_at',
                'image', 'image_file_name', 'image_file_size', 'image_content_type', 'image_updated_at',
            ],
            array_keys($info['attributes']),
            'Order of stapler attributes incorrect'
        );
    }

    /**
     * @test
     */
    function it_does_not_detect_stapler_attributes_on_a_model_that_has_none()
    {
        // Setup
        $model    = new TestActivatable;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        $info->attributes = [
            'file_file_name' => new ModelAttributeData([
                'name'     => 'file_file_name',
                'cast'     => AttributeCast::STRING,
                'nullable' => true,
            ]),
            'file_file_size' => new ModelAttributeData([
                'name'     => 'file_file_size',
                'cast'     => AttributeCast::INTEGER,
                'nullable' => true,
            ]),
            'file_content_type' => new ModelAttributeData([
                'name'     => 'file_content_type',
                'cast'     => AttributeCast::STRING,
                'nullable' => true,
            ]),
            'file_updated_at' => new ModelAttributeData([
                'name'     => 'file_updated_at',
                'cast'     => AttributeCast::DATE,
                'nullable' => true,
            ]),
        ];

        // Test
        $step = new DetectStaplerAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertArrayNotHasKey('file', $info['attributes']);
    }

}
