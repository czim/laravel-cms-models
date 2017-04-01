<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportStrategyData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Enricher\Steps\EnrichExportStrategyData;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

class EnrichExportStrategyDataTest extends TestCase
{

    /**
     * @test
     */
    function it_uses_the_key_for_strategies_where_strategy_is_not_set()
    {
        $mockEnricher = $this->getMockEnricher();

        $step = new EnrichExportStrategyData($mockEnricher);

        $info = new ModelInformation;

        $info->model          = TestPost::class;
        $info->original_model = TestPost::class;

        $info->export->strategies = [
            'csv' => new ModelExportStrategyData,
            'xml' => new ModelExportStrategyData(['strategy' => 'test']),
        ];

        $step->enrich($info, []);


        /** @var ModelExportStrategyData $strategy */
        $strategy = $info->export->strategies['csv'];
        static::assertInstanceOf(ModelExportStrategyData::class, $strategy);
        static::assertEquals('csv', $strategy->strategy);

        $strategy = $info->export->strategies['xml'];
        static::assertInstanceOf(ModelExportStrategyData::class, $strategy);
        static::assertEquals('test', $strategy->strategy);
    }


    /**
     * @return ModelInformationEnricherInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockEnricher()
    {
        return Mockery::mock(ModelInformationEnricherInterface::class);
    }

}
