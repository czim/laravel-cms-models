<?php
namespace Czim\CmsModels\Contracts\Support\Translation;

interface TranslationLocaleHelperInterface
{

    /**
     * Returns list of locales available for content transla
     *
     * @return string[]
     */
    public function availableLocales();

    /**
     * Returns default translation locale.
     *
     * @return string
     */
    public function defaultLocale();

    /**
     * Returns currently active translation locale.
     *
     * @return string
     */
    public function activeLocale();

    /**
     * Set currently active translation locale.
     *
     * @param string|null $locale   if empty/null, unsets the active locale
     * @return $this
     */
    public function setActiveLocale($locale);

}
