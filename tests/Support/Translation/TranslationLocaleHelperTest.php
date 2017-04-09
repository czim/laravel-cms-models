<?php
namespace Czim\CmsModels\Test\Support\Translation;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Support\Translation\TranslationLocaleHelper;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class TranslationLocaleHelperTest
 *
 * @group support
 * @group support-helpers
 */
class TranslationLocaleHelperTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_the_available_locales_as_configured_for_the_cms()
    {
        $coreMock = $this->getMockRouteHelper();

        $coreMock->shouldReceive('config')->with('locale.translation-locales')->andReturn(['nl', 'en']);

        $this->app->instance(Component::CORE, $coreMock);

        $helper = new TranslationLocaleHelper;

        static::assertEquals(['nl', 'en'], $helper->availableLocales());
    }

    /**
     * @test
     */
    function it_returns_the_available_locales_defaulting_to_application_locales()
    {
        $coreMock       = $this->getMockRouteHelper();
        $localeRepoMock = $this->getMockLocaleRepository();

        $coreMock->shouldReceive('config')->with('locale.translation-locales')->andReturn(null);
        $localeRepoMock->shouldReceive('getAvailable')->andReturn(['nl', 'en']);

        $this->app->instance(Component::CORE, $coreMock);
        $this->app->instance(LocaleRepositoryInterface::class, $localeRepoMock);

        $helper = new TranslationLocaleHelper;

        static::assertEquals(['nl', 'en'], $helper->availableLocales());
    }

    /**
     * @test
     */
    function it_returns_the_default_locale_as_configured_for_the_cms()
    {
        $coreMock = $this->getMockRouteHelper();

        $coreMock->shouldReceive('config')->with('locale.translation-default')->andReturn('en');

        $this->app->instance(Component::CORE, $coreMock);

        $helper = new TranslationLocaleHelper;

        static::assertEquals('en', $helper->defaultLocale());
    }

    /**
     * @test
     */
    function it_returns_the_default_locale_defaulting_to_application_locales()
    {
        $coreMock       = $this->getMockRouteHelper();
        $localeRepoMock = $this->getMockLocaleRepository();

        $coreMock->shouldReceive('config')->with('locale.translation-locales')->andReturn(['nl', 'en']);
        $coreMock->shouldReceive('config')->with('locale.translation-default')->andReturn(null);
        $localeRepoMock->shouldReceive('getAvailable')->andReturn(['nl', 'en']);

        $this->app->instance(Component::CORE, $coreMock);
        $this->app->instance(LocaleRepositoryInterface::class, $localeRepoMock);

        $helper = new TranslationLocaleHelper;

        static::assertEquals('en', $helper->defaultLocale());
    }

    /**
     * @test
     */
    function it_returns_the_default_locale_falling_back_to_the_first_of_the_available_if_none_could_be_determined()
    {
        $coreMock       = $this->getMockRouteHelper();
        $localeRepoMock = $this->getMockLocaleRepository();

        $coreMock->shouldReceive('config')->with('locale.translation-locales')->andReturn(['nl', 'fr']);
        $coreMock->shouldReceive('config')->with('locale.translation-default')->andReturn(null);
        $localeRepoMock->shouldReceive('getAvailable')->andReturn(['nl', 'en']);

        $this->app->instance(Component::CORE, $coreMock);
        $this->app->instance(LocaleRepositoryInterface::class, $localeRepoMock);

        $helper = new TranslationLocaleHelper;

        static::assertEquals('nl', $helper->defaultLocale());
    }

    /**
     * @test
     */
    function it_returns_the_active_locale_from_session_if_available()
    {
        $coreMock = $this->getMockRouteHelper();

        session()->put(TranslationLocaleHelper::ACTIVE_LOCALE_SESSION_KEY, 'nl');

        $coreMock->shouldReceive('config')->with('locale.translation-locales')->andReturn(['nl', 'en']);
        $coreMock->shouldReceive('session')->andReturn(session());

        $this->app->instance(Component::CORE, $coreMock);

        $helper = new TranslationLocaleHelper;

        static::assertEquals('nl', $helper->activeLocale());
    }

    /**
     * @test
     */
    function it_returns_the_active_locale_falling_back_to_default_locale()
    {
        $coreMock = $this->getMockRouteHelper();

        $coreMock->shouldReceive('config')->with('locale.translation-default')->andReturn('en');
        $coreMock->shouldReceive('session')->andReturn(session());

        $this->app->instance(Component::CORE, $coreMock);

        $helper = new TranslationLocaleHelper;

        static::assertEquals('en', $helper->activeLocale());
    }

    /**
     * @test
     */
    function it_sets_the_active_locale()
    {
        $coreMock = $this->getMockRouteHelper();

        $coreMock->shouldReceive('config')->with('locale.translation-default')->andReturn('en');
        $coreMock->shouldReceive('config')->with('locale.translation-locales')->andReturn(['nl', 'en']);
        $coreMock->shouldReceive('session')->andReturn(session());

        $this->app->instance(Component::CORE, $coreMock);

        $helper = new TranslationLocaleHelper;

        static::assertSame($helper, $helper->setActiveLocale('nl'));

        static::assertEquals('nl', $helper->activeLocale());

        // Test whether default is returned after unsetting it
        $helper->setActiveLocale(null);

        static::assertEquals('en', $helper->activeLocale());
    }


    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockRouteHelper()
    {
        return Mockery::mock(CoreInterface::class);
    }

    /**
     * @return LocaleRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockLocaleRepository()
    {
        return Mockery::mock(LocaleRepositoryInterface::class);
    }

}
