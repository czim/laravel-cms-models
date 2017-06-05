<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormHelpTextData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelFormHelpTextDataTest
 *
 * @group modelinformation-data
 */
class ModelFormHelpTextDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_untranslated_text()
    {
        $data = new ModelFormHelpTextData;

        $data->text = 'Testing';

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Testing', $data->text());
    }

    /**
     * @test
     */
    function it_returns_translated_text()
    {
        $data = new ModelFormHelpTextData;

        $data->text_translated = 'testing.translation';
        $data->text            = 'Testing';

        $this->app->setLocale('en');
        $this->app['translator']->addLines(['testing.translation' => 'Exists'], 'en', '*');

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Exists', $data->text());
    }
    
    /**
     * @test
     */
    function it_returns_an_icon()
    {
        $data = new ModelFormHelpTextData;

        $data->icon = 'testing';

        static::assertEquals('testing', $data->icon());
    }

    /**
     * @test
     */
    function it_returns_a_style_class()
    {
        $data = new ModelFormHelpTextData;

        $data->class = 'testing';

        static::assertEquals('testing', $data->cssClass());
    }

    /**
     * @test
     */
    function it_returns_a_view_partial()
    {
        $data = new ModelFormHelpTextData;

        $data->view = 'testing';

        static::assertEquals('testing', $data->view());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelFormHelpTextData;

        $data->text = 'testing';
        $data->icon = 'first';

        $with = new ModelFormHelpTextData;

        $with->text_translated = 'translated';
        $with->icon            = 'second';

        $data->merge($with);

        static::assertEquals('testing', $data->text);
        static::assertEquals('translated', $data->text_translated);
        static::assertEquals('second', $data->icon);
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

}
