<?php
namespace Czim\CmsModels\Test\Support\Validation;

use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;
use Czim\CmsModels\Support\Validation\ValidationRuleDecorator;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ValidationRuleDecoratorTest
 *
 * @group support
 * @group support-validation
 */
class ValidationRuleDecoratorTest extends TestCase
{

    /**
     * @test
     */
    function it_decorates_model_key_placeholder_if_model_is_given()
    {
        $rules = [
            'one' => 'required|unique:table,id,<key>',
            'two' => [
                'min:6',
                'unique:table,id,<key>',
            ],
        ];

        $mockLocaleHelper = $this->getMockLocaleHelper();
        $mockLocaleHelper->shouldReceive('availableLocales')->andReturn([]);

        $this->app->instance(TranslationLocaleHelperInterface::class, $mockLocaleHelper);

        $decorator = new ValidationRuleDecorator();

        $model = new TestPost;
        $model->setAttribute('id', 1);

        $output = $decorator->decorate($rules, $model);

        static::assertInternalType('array', $output);
        static::assertArrayHasKey('one', $output);
        static::assertArrayHasKey('two', $output);
        static::assertEquals('required|unique:table,id,1', $output['one']);
        static::assertEquals(['min:6', 'unique:table,id,1'], $output['two']);
    }

    /**
     * @test
     */
    function it_leaves_model_key_placeholder_untouched_if_no_model_is_given()
    {
        $rules = [
            'one' => 'required|unique:table,id,<key>',
            'two' => [
                'min:6',
                'unique:table,id,<key>',
            ],
        ];

        $mockLocaleHelper = $this->getMockLocaleHelper();
        $mockLocaleHelper->shouldReceive('availableLocales')->andReturn([]);

        $this->app->instance(TranslationLocaleHelperInterface::class, $mockLocaleHelper);

        $decorator = new ValidationRuleDecorator();

        $output = $decorator->decorate($rules);

        static::assertInternalType('array', $output);
        static::assertArrayHasKey('one', $output);
        static::assertArrayHasKey('two', $output);
        static::assertEquals('required|unique:table,id,<key>', $output['one']);
        static::assertEquals(['min:6', 'unique:table,id,<key>'], $output['two']);
    }

    /**
     * @test
     */
    function it_decorates_translation_rules_for_active_locales()
    {
        $rules = [
            'one.<trans>.title' => 'required',
            'two.<trans>.name' => ['min:6'],
        ];

        $mockLocaleHelper = $this->getMockLocaleHelper();
        $mockLocaleHelper->shouldReceive('availableLocales')->andReturn(['nl', 'en']);

        $this->app->instance(TranslationLocaleHelperInterface::class, $mockLocaleHelper);

        $decorator = new ValidationRuleDecorator();

        $output = $decorator->decorate($rules);

        static::assertInternalType('array', $output);
        static::assertArrayHasKey('one.nl.title', $output);
        static::assertArrayHasKey('one.en.title', $output);
        static::assertArrayHasKey('two.nl.name', $output);
        static::assertArrayHasKey('two.en.name', $output);
        static::assertEquals('required', $output['one.nl.title']);
        static::assertEquals('required', $output['one.en.title']);
        static::assertEquals(['min:6'], $output['two.nl.name']);
        static::assertEquals(['min:6'], $output['two.en.name']);
    }

    /**
     * @return TranslationLocaleHelperInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockLocaleHelper()
    {
        return Mockery::mock(TranslationLocaleHelperInterface::class);
    }

}
