<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Export;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Export\ModelExportStrategyData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelExportStrategyDataTest
 *
 * @group modelinformation-data
 */
class ModelExportStrategyDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_untranslated_label()
    {
        $data = new ModelExportStrategyData;

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
        $data = new ModelExportStrategyData;

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
    function it_falls_back_to_decorated_strategy_for_label()
    {
        $data = new ModelExportStrategyData;

        $data->strategy = 'strategy';

        static::assertEquals('Strategy', $data->label());
    }
    
    /**
     * @test
     */
    function it_returns_the_icon()
    {
        $data = new ModelExportStrategyData;

        static::assertEmpty($data->icon());

        $data->icon = 'home';

        static::assertEquals('home', $data->icon());
    }

    /**
     * @test
     */
    function it_returns_permissions_as_array()
    {
        $data = new ModelExportStrategyData();

        $data->permissions = 'a';

        static::assertEquals(['a'], $data->permissions());

        $data->permissions = ['a'];

        static::assertEquals(['a'], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_false_for_permissions_if_null()
    {
        $data = new ModelExportStrategyData();

        $data->permissions = null;

        static::assertFalse($data->permissions());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelExportStrategyData;

        $data->icon = 'home';
        $data->columns = [
            'a' => [
                'source' => 'a'
            ],
            'b' => [
                'source' => 'b'
            ],
        ];

        $with = new ModelExportStrategyData;

        $with->icon = 'replace';
        $with->columns = [
            'a' => [
                'source' => 'x'
            ],
            'c' => [
                'source' => 'y'
            ],
        ];

        $data->merge($with);

        static::assertEquals('replace', $data->icon);
        static::assertEquals(['a', 'c'], array_keys($data->columns));
        static::assertEquals('x', $data->columns['a']->source);
        static::assertEquals('y', $data->columns['c']->source);
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }
    
}
