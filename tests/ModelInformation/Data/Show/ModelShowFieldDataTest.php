<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Show;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Show\ModelShowFieldData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelShowFieldDataTest
 *
 * @group modelinformation-data
 */
class ModelShowFieldDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_untranslated_label()
    {
        $data = new ModelShowFieldData;

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
        $data = new ModelShowFieldData;

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
    function it_falls_back_to_decorated_source_for_label()
    {
        $data = new ModelShowFieldData;

        $data->source = 'source';

        static::assertEquals('Source', $data->label());
    }

    /**
     * @test
     */
    function it_returns_the_source()
    {
        $data = new ModelShowFieldData;

        $data->source = 'source';

        static::assertEquals('source', $data->source());
    }

    /**
     * @test
     */
    function it_returns_whether_it_is_translated()
    {
        $data = new ModelShowFieldData;

        $data->translated = null;

        static::assertFalse($data->translated());

        $data->translated = true;

        static::assertTrue($data->translated());
    }

    /**
     * @test
     */
    function it_returns_options_as_array()
    {
        $data = new ModelShowFieldData;

        $data->options = null;

        static::assertEquals([], $data->options());

        $data->options = ['a'];

        static::assertEquals(['a'], $data->options());
    }

    /**
     * @test
     */
    function it_returns_permissions_as_array()
    {
        $data = new ModelShowFieldData;

        $data->permissions = null;

        static::assertEquals([], $data->permissions());

        $data->permissions = 'a';

        static::assertEquals(['a'], $data->permissions());

        $data->permissions = ['a'];

        static::assertEquals(['a'], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_whether_the_field_is_admin_only()
    {
        $data = new ModelShowFieldData;

        $data->admin_only = null;

        static::assertFalse($data->adminOnly());

        $data->admin_only = true;

        static::assertTrue($data->adminOnly());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelShowFieldData;

        $data->strategy = 'test';
        $data->options  = [
            'a' => 'x',
        ];

        $with = new ModelShowFieldData;

        $with->strategy = 'replace';
        $with->options  = [
            'b' => 'y',
        ];

        $data->merge($with);

        static::assertEquals('replace', $data->strategy);
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
