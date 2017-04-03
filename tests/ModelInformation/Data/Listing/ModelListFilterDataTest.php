<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Listing;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelListFilterDataTest
 *
 * @group modelinformation-data
 */
class ModelListFilterDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_untranslated_label()
    {
        $data = new ModelListFilterData;

        $data->label = 'Testing';

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Testing', $data->label());
    }

    /**
     * @test
     */
    function it_returns_translated_label()
    {
        $data = new ModelListFilterData;

        $data->label_translated = 'testing.translation';
        $data->label            = 'Testing';

        $this->app->setLocale('en');
        $this->app['translator']->addLines(['testing.translation' => 'Exists'], 'en', '*');

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Exists', $data->label());
    }

    /**
     * @test
     */
    function it_returns_source()
    {
        $data = new ModelListFilterData;

        $data->source = 'testing';

        static::assertEquals('testing', $data->source());
    }

    /**
     * @test
     */
    function it_returns_target()
    {
        $data = new ModelListFilterData;

        $data->target = 'testing';

        static::assertEquals('testing', $data->target());
    }

    /**
     * @test
     */
    function it_returns_strategy()
    {
        $data = new ModelListFilterData;

        $data->strategy = 'testing';

        static::assertEquals('testing', $data->strategy());
    }

    /**
     * @test
     */
    function it_returns_options_as_array()
    {
        $data = new ModelListFilterData;

        $data->options = null;

        static::assertEquals([], $data->options());

        $data->options = ['a'];

        static::assertEquals(['a'], $data->options());
    }
    
    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelListFilterData;

        $data->label  = 'test';
        $data->options = ['a' => 'x'];

        $with = new ModelListFilterData;

        $with->label = 'replaced';
        $with->options = ['b' => 'y'];

        $data->merge($with);

        static::assertEquals('replaced', $data->label);
        static::assertEquals(['a' => 'x', 'b' => 'y'], $data->options);
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }
    
}
