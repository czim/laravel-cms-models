<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Analyzer\UsesAbstractTraitAnalyzerStep;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestOrderable;

/**
 * Class AbstractTraitAnalyzerTest
 *
 * @group analysis
 */
class AbstractTraitAnalyzerTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_returns_traits_used_by_a_model()
    {
        // Setup
        $model    = new TestOrderable;
        $analyzer = $this->prepareAnalyzerSetup($model);

        // Test
        $step = new UsesAbstractTraitAnalyzerStep;
        $step->setAnalyzer($analyzer);

        static::assertEquals(['Czim\Listify\Listify'], array_values($step->publicGetTraitNames()));
    }

    /**
     * @test
     */
    function it_returns_that_a_trait_is_used_by_a_model()
    {
        // Setup
        $model    = new TestOrderable;
        $analyzer = $this->prepareAnalyzerSetup($model);

        // Test
        $step = new UsesAbstractTraitAnalyzerStep;
        $step->setAnalyzer($analyzer);

        static::assertTrue($step->publicModelHasTrait('Czim\Listify\Listify'));
    }

    /**
     * @test
     */
    function it_returns_that_a_list_of_traits_is_used_by_a_model()
    {
        // Setup
        $model    = new TestOrderable;
        $analyzer = $this->prepareAnalyzerSetup($model);

        // Test
        $step = new UsesAbstractTraitAnalyzerStep;
        $step->setAnalyzer($analyzer);

        static::assertTrue($step->publicModelHasTrait(['Czim\Listify\Listify']));
    }

    /**
     * @test
     */
    function it_can_add_default_includes()
    {
        // Setup
        $model    = new TestOrderable;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        $step = new UsesAbstractTraitAnalyzerStep;
        $step->setAnalyzer($analyzer);
        $step->analyze($info);


        static::assertEmpty($step->getTestInformation()['includes']['default']);

        $step->publicAddIncludesDefault('testing');

        static::assertEquals(['testing'], $step->getTestInformation()['includes']['default']);

        $step->publicAddIncludesDefault('withValue', 'value');

        static::assertEquals(['testing', 'withValue' => 'value'], $step->getTestInformation()['includes']['default']);
    }

}
