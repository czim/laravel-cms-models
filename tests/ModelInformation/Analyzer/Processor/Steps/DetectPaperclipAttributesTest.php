<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps\DetectAttachmentAttributes;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestActivatable;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestPaperclip;

/**
 * Class DetectPaperclipAttributesTest
 *
 * @group analysis
 */
class DetectPaperclipAttributesTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_detects_paperclip_attributes()
    {
        // Setup
        $model    = new TestPaperclip;
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
        $step = new DetectAttachmentAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertArrayHasKey('file', $info['attributes'], 'Paperclip attribute not added');
        static::assertArrayHasKey('image', $info['attributes'], 'Paperclip attribute not added');

        static::assertEquals(
            [
                'file', 'file_file_name', 'file_file_size', 'file_content_type', 'file_updated_at',
                'image', 'image_file_name', 'image_file_size', 'image_content_type', 'image_updated_at',
            ],
            array_keys($info['attributes']),
            'Order of paperclip attributes incorrect'
        );
    }

    /**
     * @test
     */
    function it_does_not_detect_paperclip_attributes_on_a_model_that_has_none()
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
        $step = new DetectAttachmentAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertArrayNotHasKey('file', $info['attributes']);
    }

}
