<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps\DetectOrderable;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestActivatable;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestOrderable;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestOrderableBelongsToScoped;

/**
 * Class DetectOrderableTest
 *
 * @group analysis
 */
class DetectOrderableTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_detects_a_model_with_the_listify_trait_as_orderable()
    {
        // Setup
        $model    = new TestOrderable;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        // Test
        $step = new DetectOrderable;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertTrue($info['list']['orderable']);
        static::assertEquals('listify', $info['list']['order_strategy']);
        static::assertEquals('position', $info['list']['order_column']);
    }

    /**
     * @test
     */
    function it_detects_a_model_with_the_listify_trait_as_scoped_with_belongs_to_relation()
    {
        // Setup
        $model    = new TestOrderableBelongsToScoped;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        $info['relations'] = [
            'testActivatable' => new ModelRelationData([
                'name'   => 'testActivatable',
                'method' => 'testActivatable',
                'type'   => RelationType::BELONGS_TO,
            ]),
        ];

        // Test
        $step = new DetectOrderable;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertTrue($info['list']['orderable']);
        static::assertEquals('listify', $info['list']['order_strategy']);
        static::assertEquals('position', $info['list']['order_column']);
        static::assertEquals('testActivatable', $info['list']['order_scope_relation']);
    }

    /**
     * @test
     */
    function it_detects_a_model_without_the_listify_trait_as_not_orderable()
    {
        // Setup
        $model    = new TestActivatable;
        $analyzer = $this->prepareAnalyzerSetup($model);
        $info     = new ModelInformation;

        // Test
        $step = new DetectOrderable;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertFalse($info['list']['orderable']);
    }

}
