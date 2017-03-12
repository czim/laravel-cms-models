<?php
namespace Czim\CmsModels\Test\Support\Strategies\Traits;

use Czim\CmsModels\Test\AbstractSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Support\SourceMethodTestHelper;
use Czim\CmsModels\Test\Helpers\Support\UsesResolvesSourceStrategies;

class ResolvesSourceStrategiesTest extends AbstractSeededTestCase
{

    /**
     * @test
     */
    function it_resolves_a_direct_attribute()
    {
        $instance = new UsesResolvesSourceStrategies;

        $model = new TestPost([
            'type' => 'test type',
        ]);

        static::assertEquals('test type', $instance->publicResolveModelSource($model, 'type'));
    }

    /**
     * @test
     */
    function it_resolves_a_translated_attribute()
    {
        $instance = new UsesResolvesSourceStrategies;

        $model = TestPost::create([
            'en' => [
                'title' => 'test title en',
                'body'  => 'body en',
            ],
            'nl' => [
                'title' => 'test title nl',
                'body'  => 'body nl',
            ],
        ]);

        $this->app->setLocale('en');
        static::assertEquals('test title en', $instance->publicResolveModelSource($model, 'title'));
        $this->app->setLocale('nl');
        static::assertEquals('test title nl', $instance->publicResolveModelSource($model, 'title'));
    }

    /**
     * @test
     */
    function it_parses_an_instantiable_class_and_method_combination()
    {
        $instance = new UsesResolvesSourceStrategies;
        $method   = SourceMethodTestHelper::class . '@source';

        $model = new TestPost([
            'type' => 'test type',
        ]);

        // Make the method access the 'type' attribute
        $this->app->instance('source-method-test-helper-method', 'type');

        static::assertEquals('test type', $instance->publicResolveModelSource($model, $method));
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\StrategyResolutionException
     */
    function it_throws_an_exception_if_the_class_of_an_indicated_class_method_pair_does_not_exist()
    {
        $instance = new UsesResolvesSourceStrategies;
        $method   = 'SomeClass\DoesNotExist@doesNotExist';

        $model = new TestPost;

        $instance->publicResolveModelSource($model, $method);
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\StrategyResolutionException
     */
    function it_throws_an_exception_if_the_method_of_an_indicated_class_method_pair_does_not_exist()
    {
        $instance = new UsesResolvesSourceStrategies;
        $method   = SourceMethodTestHelper::class . '@doesNotExist';

        $model = new TestPost;

        $instance->publicResolveModelSource($model, $method);
    }

    /**
     * @test
     */
    function it_calls_a_public_method_on_the_model()
    {
        $instance = new UsesResolvesSourceStrategies;

        $model = new TestPost;

        static::assertEquals('testing method value', $instance->publicResolveModelSource($model, '@testMethod'));
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\StrategyResolutionException
     */
    function it_throws_an_exception_if_an_indicated_method_does_not_exist()
    {
        $instance = new UsesResolvesSourceStrategies;

        $model = new TestPost;

        $instance->publicResolveModelSource($model, '@methodDoesNotExist');
    }

    /**
     * @test
     */
    function it_uses_a_public_method_on_the_model_if_the_source_matches_its_name()
    {
        $instance = new UsesResolvesSourceStrategies;

        $model = new TestPost;

        static::assertEquals('testing method value', $instance->publicResolveModelSource($model, 'testMethod'));
    }

}
