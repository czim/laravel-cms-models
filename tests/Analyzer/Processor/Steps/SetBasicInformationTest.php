<?php
namespace Czim\CmsModels\Test\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps\SetBasicInformation;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class SetBasicInformationTest
 *
 * @group analysis
 */
class SetBasicInformationTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_sets_information_based_on_the_model()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        // Test
        $step = new SetBasicInformation;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals('test post', $info['verbose_name']);
        static::assertEquals('test posts', $info['verbose_name_plural']);
        static::assertEquals('models.name.test post', $info['translated_name']);
        static::assertEquals('models.name.test posts', $info['translated_name_plural']);

        static::assertTrue($info['incrementing']);
    }

}
