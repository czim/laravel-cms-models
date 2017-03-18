<?php
namespace Czim\CmsModels\Test\Analyzer\Processor;

use Czim\CmsModels\Analyzer\Processor\ModelAnalyzer;
use Czim\CmsModels\Test\TestCase;

class ModelAnalyzerTest extends TestCase
{

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #does not exist#i
     */
    function it_throws_an_exception_when_asked_to_analyze_a_class_that_does_not_exist()
    {
        $analyzer = new ModelAnalyzer;
        $analyzer->analyze('Does\\NotExist\\AtAll');
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #not a model#i
     */
    function it_throws_an_exception_when_asked_to_analyze_something_other_than_a_model()
    {
        $analyzer = new ModelAnalyzer;
        $analyzer->analyze(static::class);
    }

}
