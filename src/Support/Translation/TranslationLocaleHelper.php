<?php
namespace Czim\CmsModels\Support\Translation;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;

/**
 * Class TranslationLocaleHelper
 *
 * For managing and tracking the locales available or active for model translations.
 */
class TranslationLocaleHelper implements TranslationLocaleHelperInterface
{
    const ACTIVE_LOCALE_SESSION_KEY = 'active-translation-locale';


    /**
     * Returns list of locales available for content transla
     *
     * @return string[]
     */
    public function availableLocales()
    {
        $locales = $this->getCore()->config('locale.translation-locales');

        if (is_array($locales)) {
            return $locales;
        }

        return $this->getLocaleRepository()->getAvailable();
    }

    /**
     * Returns default translation locale.
     *
     * @return string
     */
    public function defaultLocale()
    {
        $locale = $this->getCore()->config('locale.translation-default');

        if ($locale) {
            return $locale;
        }

        // Fall back to default based on CMS configuration and app locale.
        $locale    = app()->getLocale();
        $available = $this->availableLocales();

        // If no translation locales are set, use app's locale as default.
        // If current app locale is available, use it as default.
        if ( ! count($available) || in_array($locale, $available)) {
            return $locale;
        }

        return head($available);
    }

    /**
     * Returns currently active translation locale.
     *
     * @return string
     */
    public function activeLocale()
    {
        $active = $this->getCore()->session()->get(static::ACTIVE_LOCALE_SESSION_KEY);

        if ($active && in_array($active, $this->availableLocales())) {
            return $active;
        }

        return $this->defaultLocale();
    }

    /**
     * Set currently active translation locale.
     *
     * @param string|null $locale   if empty/null, unsets the active locale
     * @return $this
     */
    public function setActiveLocale($locale)
    {
        if (empty($locale)) {
            $this->getCore()->session()->forget(static::ACTIVE_LOCALE_SESSION_KEY);
        } else {
            $this->getCore()->session()->put(static::ACTIVE_LOCALE_SESSION_KEY, $locale);
        }

        return $this;
    }


    /**
     * @return LocaleRepositoryInterface
     */
    protected function getLocaleRepository()
    {
        return app(LocaleRepositoryInterface::class);
    }

    /**
     * @return CoreInterface
     */
    protected function getCore()
    {
        return app(Component::CORE);
    }

}
