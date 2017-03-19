<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps\AnalyzeScopes;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelScopeData;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestScopes;

/**
 * Class AnalyzeScopesTest
 *
 * @group analysis
 */
class AnalyzeScopesTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_analyzes_scopes_ignoring_invalid_scopes()
    {
        // Setup
        $model    = new TestScopes;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        // Test
        $step = new AnalyzeScopes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertInternalType('array', $info['list']['scopes']);
        static::assertEquals(['normal'], array_keys($info['list']['scopes']));

        /** @var ModelScopeData $scope */
        $scope = $info['list']['scopes']['normal'];
        static::assertInstanceOf(ModelScopeData::class, $scope);
        static::assertEquals('normal', $scope->method);
    }

}
