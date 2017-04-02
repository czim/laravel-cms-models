<?php
namespace Czim\CmsModels\Test\ModelInformation\Data;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelInformationTest
 *
 * @group modelinformation-data
 */
class ModelInformationTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_the_model_class()
    {
        $data = new ModelInformation;

        $data->model          = 'testing';
        $data->original_model = 'testing';

        static::assertEquals('testing', $data->modelClass());
    }

    /**
     * @test
     */
    function it_returns_an_untranslated_label()
    {
        $data = new ModelInformation;

        $data->translated_name = 'testing.key';
        $data->verbose_name    = 'Testing';

        static::assertEquals('Testing', $data->label(false));
    }

    /**
     * @test
     */
    function it_returns_a_translated_label()
    {
        $data = new ModelInformation;

        $this->app->setLocale('en');
        $this->app['translator']->addLines(['testing.translation' => 'Exists'], 'en', '*');

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        $data->translated_name = 'testing.translation';
        $data->verbose_name    = 'Testing';

        static::assertEquals('Exists', $data->label(true));
    }

    /**
     * @test
     */
    function it_returns_the_label_translation_key()
    {
        $data = new ModelInformation;

        $data->translated_name = 'testing.key';

        static::assertEquals('testing.key', $data->labelTranslationKey());
    }

    /**
     * @test
     */
    function it_returns_an_untranslated_plural_label()
    {
        $data = new ModelInformation;

        $data->translated_name_plural = 'testing.key';
        $data->verbose_name_plural    = 'Testing';

        static::assertEquals('Testing', $data->labelPlural(false));
    }

    /**
     * @test
     */
    function it_returns_a_translated_plural_label()
    {
        $data = new ModelInformation;

        $this->app->setLocale('en');
        $this->app['translator']->addLines(['testing.translation' => 'Exists'], 'en', '*');

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        $data->translated_name_plural = 'testing.translation';
        $data->verbose_name_plural    = 'Testing';

        static::assertEquals('Exists', $data->labelPlural(true));
    }

    /**
     * @test
     */
    function it_returns_the_plural_label_translation_key()
    {
        $data = new ModelInformation;

        $data->translated_name_plural = 'testing.key';

        static::assertEquals('testing.key', $data->labelPluralTranslationKey());
    }

    /**
     * @test
     */
    function it_returns_whether_deletion_is_allowed()
    {
        $data = new ModelInformation;

        static::assertTrue($data->allowDelete(), 'Allow delete should default to true');

        $data->allow_delete = false;

        static::assertFalse($data->allowDelete());
    }

    /**
     * @test
     */
    function it_returns_the_delete_condition()
    {
        $data = new ModelInformation;

        static::assertFalse($data->deleteCondition(), 'Delete condition should assume false if null');

        $data->delete_condition = 'testing';

        static::assertEquals('testing', $data->deleteCondition());
    }

    /**
     * @test
     */
    function it_returns_the_delete_strategy()
    {
        $data = new ModelInformation;

        static::assertFalse($data->deleteStrategy(), 'Delete strategy should assume false if null');

        $data->delete_strategy = 'testing';

        static::assertEquals('testing', $data->deleteStrategy());
    }

    /**
     * @test
     */
    function it_returns_whether_deletion_must_be_confirmed()
    {
        $data = new ModelInformation;

        $this->app['config']->set('cms-models.defaults.confirm_delete', true);

        static::assertTrue($data->confirmDelete(), 'Delete confirmation should default to config value');

        $data->confirm_delete = false;

        static::assertFalse($data->confirmDelete());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelInformation;

        $data->model          = 'test';
        $data->original_model = 'test';
        $data->single         = false;
        $data->verbose_name   = 'Testing';

        $with = new ModelInformation;

        $with->model          = 'new';
        $with->original_model = 'replace';
        $with->single         = true;

        $data->merge($with);

        static::assertEquals('new', $data->model);
        static::assertEquals('replace', $data->original_model);
        static::assertTrue($data->single);
        static::assertEquals('Testing', $data->verbose_name);

        // It should not replace empty model data
        $data = new ModelInformation;

        $data->model          = 'test';
        $data->original_model = 'test';

        $with = new ModelInformation;

        $with->model          = '';
        $with->original_model = null;

        static::assertEquals('test', $data->model);
        static::assertEquals('test', $data->original_model);
    }

    /**
     * @test
     */
    function it_merges_nested_dataobjects()
    {
        $data = new ModelInformation;

        $data->form->layout = [
            'test',
        ];

        $with = new ModelInformation;

        $with->form->layout = [
            'replaced',
        ];

        $data->merge($with);

        static::assertEquals(['replaced'], $data->form->layout);
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\ModelConfigurationDataException
     * @expectedExceptionMessageRegExp #unknown_property#
     */
    function it_throws_an_exception_if_an_unknown_property_is_set()
    {
        $data = new ModelInformation;

        $data->unknown_property = true;
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\ModelConfigurationDataException
     * @expectedExceptionMessageRegExp #unknown_property#
     */
    function it_throws_an_exception_if_nested_data_is_invalid_for_lazy_loaded_object()
    {
        $data = new ModelInformation;

        $data->meta = [
            'unknown_property' => true,
        ];

        $data->meta;
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

}
