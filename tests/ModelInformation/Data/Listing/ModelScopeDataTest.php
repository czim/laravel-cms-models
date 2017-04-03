<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Listing;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelScopeData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelScopeDataTest
 *
 * @group modelinformation-data
 */
class ModelScopeDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_untranslated_label()
    {
        $data = new ModelScopeData;

        $data->label = 'Testing';

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Testing', $data->display());
    }

    /**
     * @test
     */
    function it_returns_translated_label()
    {
        $data = new ModelScopeData;

        $data->label_translated = 'testing.translation';
        $data->label            = 'Testing';

        $this->app->setLocale('en');
        $this->app['translator']->addLines(['testing.translation' => 'Exists'], 'en', '*');

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Exists', $data->display());
    }

    /**
     * @test
     */
    function it_falls_back_to_decorated_method_for_label()
    {
        $data = new ModelScopeData;

        $data->method = 'sourceMethod';

        static::assertEquals('source method', $data->display());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelScopeData;

        $data->strategy = 'test';
        $data->label    = 'test';

        $with = new ModelScopeData;

        $with->strategy = 'replaced';
        $with->label    = 'replaced';

        $data->merge($with);

        static::assertEquals('replaced', $data->strategy);
        static::assertEquals('replaced', $data->label);
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

}
