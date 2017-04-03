<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelFormFieldDataTest
 *
 * @group modelinformation-data
 */
class ModelFormFieldDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_its_key()
    {
        $data = new ModelFormFieldData;

        $data->key = 'testing';

        static::assertEquals('testing', $data->key());
    }

    /**
     * @test
     */
    function it_returns_whether_to_be_included_for_create()
    {
        $data = new ModelFormFieldData;

        static::assertTrue($data->create());

        $data->create = false;

        static::assertFalse($data->create());
    }

    /**
     * @test
     */
    function it_returns_whether_to_be_included_for_update()
    {
        $data = new ModelFormFieldData;

        static::assertTrue($data->update());

        $data->update = false;

        static::assertFalse($data->update());
    }

    /**
     * @test
     */
    function it_returns_untranslated_label()
    {
        $data = new ModelFormFieldData;

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
        $data = new ModelFormFieldData;

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
    function it_falls_back_to_decorated_key_for_label()
    {
        $data = new ModelFormFieldData;

        $data->key = 'keyValue';

        static::assertEquals('Key value', $data->label());
    }

    /**
     * @test
     */
    function it_returns_the_source()
    {
        $data = new ModelFormFieldData;

        $data->source = 'source';

        static::assertEquals('source', $data->source());
    }

    /**
     * @test
     */
    function it_falls_back_to_key_for_empty_source()
    {
        $data = new ModelFormFieldData;

        $data->key = 'key';

        static::assertEquals('key', $data->source());
    }

    /**
     * @test
     */
    function it_returns_whether_it_is_required()
    {
        $data = new ModelFormFieldData;

        static::assertFalse($data->required());

        $data->required = true;

        static::assertTrue($data->required());
    }

    /**
     * @test
     */
    function it_returns_whether_it_is_translated()
    {
        $data = new ModelFormFieldData;

        static::assertFalse($data->translated());

        $data->translated = true;

        static::assertTrue($data->translated());
    }

    /**
     * @test
     */
    function it_returns_options_as_array()
    {
        $data = new ModelFormFieldData;

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
        $data = new ModelFormFieldData;

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
    function it_returns_whether_it_is_admin_only()
    {
        $data = new ModelFormFieldData;

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
        $data = new ModelFormFieldData;

        $data->display_strategy = 'test';
        $data->options  = [
            'a' => 'x',
        ];

        $with = new ModelFormFieldData;

        $with->display_strategy = 'replace';
        $with->options  = [
            'b' => 'y',
        ];

        $data->merge($with);

        static::assertEquals('replace', $data->display_strategy);
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
