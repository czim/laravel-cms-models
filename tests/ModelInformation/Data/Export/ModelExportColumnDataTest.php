<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Export;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportColumnData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelExportColumnDataTest
 *
 * @group modelinformation-data
 */
class ModelExportColumnDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_untranslated_header()
    {
        $data = new ModelExportColumnData;

        $data->header = 'Testing';

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Testing', $data->header());
    }

    /**
     * @test
     */
    function it_returns_translated_header()
    {
        $data = new ModelExportColumnData;

        $data->header_translated = 'testing.translation';
        $data->header            = 'Testing';

        $this->app->setLocale('en');
        $this->app['translator']->addLines(['testing.translation' => 'Exists'], 'en', '*');

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Exists', $data->header());
    }

    /**
     * @test
     */
    function it_falls_back_to_decorated_source_for_header()
    {
        $data = new ModelExportColumnData;

        $data->source = 'source';

        static::assertEquals('Source', $data->header());
    }

    /**
     * @test
     */
    function it_returns_options_as_array()
    {
        $data = new ModelExportColumnData;

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
        $data = new ModelExportColumnData;

        $data->header  = 'test';
        $data->options = ['a' => 'x'];

        $with = new ModelExportColumnData;

        $with->header = 'replaced';
        $with->options = ['b' => 'y'];

        $data->merge($with);

        static::assertEquals('replaced', $data->header);
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
