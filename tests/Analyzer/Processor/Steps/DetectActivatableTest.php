<?php
namespace Czim\CmsModels\Test\Analyzer\Processor\Steps;

use Czim\CmsModels\Analyzer\Processor\Steps\DetectActivatable;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class DetectActivatableTest
 *
 * @group analysis
 */
class DetectActivatableTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_detects_an_active_boolean_column_as_activatable()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        $info->attributes = [
            'active' => new ModelAttributeData([
                'name' => 'active',
                'cast' => AttributeCast::BOOLEAN,
            ]),
        ];

        // Test
        $step = new DetectActivatable;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertTrue($info['list']['activatable']);
        static::assertEquals('active', $info['list']['active_column']);
    }

    /**
     * @test
     */
    function it_detects_an_active_non_boolean_column_not_as_activatable()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        $info->attributes = [
            'active' => new ModelAttributeData([
                'name' => 'active',
                'cast' => AttributeCast::STRING,
            ]),
        ];

        // Test
        $step = new DetectActivatable();
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertFalse($info['list']['activatable']);
    }

    /**
     * @test
     */
    function it_detects_a_model_without_an_active_column_not_as_activatable()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        $info->attributes = [
            'test' => new ModelAttributeData([
                'name' => 'test',
                'cast' => AttributeCast::BOOLEAN,
            ]),
            'name' => new ModelAttributeData([
                'name' => 'name',
                'cast' => AttributeCast::STRING,
            ]),
        ];

        // Test
        $step = new DetectActivatable();
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertFalse($info['list']['activatable']);
    }

}
