<?php
namespace Czim\CmsModels\Test\Analyzer\Processor\Steps;

use Czim\CmsModels\Contracts\Analyzer\ModelAnalyzerInterface;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use ReflectionClass;

abstract class AbstractStepCase extends TestCase
{

    /**
     * @param Model $model
     * @return ModelAnalyzerInterface|ModelAnalyzerInterface|Mockery\Mock
     */
    protected function prepareAnalyzerSetup(Model $model)
    {
        /** @var ModelAnalyzerInterface|Mockery\Mock $analyzer */
        $analyzer = Mockery::mock(ModelAnalyzerInterface::class);
        $analyzer->shouldReceive('model')->andReturn($model);
        $analyzer->shouldReceive('reflection')->andReturn(new ReflectionClass($model));

        return $analyzer;
    }

}
