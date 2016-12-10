<?php
namespace Czim\CmsModels\Support\Translation;

use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;

trait DecoratesTranslatedValidationRules
{


    /**
     * Returns validation rules decorated for translated placeholders.
     *
     * @param array $rules
     * @return array
     */
    protected function decorateTranslatedValidationRules(array $rules)
    {
        $locales     = $this->getTranslationLocales();
        $placeholder = $this->getTranslatedRulePlaceHolder();
        $processed   = [];

        // Detect and duplicate special rules for translated locales
        foreach ($rules as $key => $ruleSet) {

            if (false === strpos($key, $placeholder)) {
                $processed[ $key ] = $ruleSet;
                continue;
            }

            // Add the rule for each translatable locale separately
            foreach ($locales as $locale) {

                $localizedKey  = str_replace($placeholder, $locale, $key);
                $localizedRule = $ruleSet;

                // Replace placeholders for rulesets, whether they formatted as string or array
                if (is_array($localizedRule)) {
                    $localizedRule = array_map(
                        function ($part) use ($placeholder, $locale) {
                            return str_replace($placeholder, $locale, $part);
                        },
                        $localizedRule
                    );
                } else {
                    $localizedRule = str_replace($placeholder, $locale, $localizedRule);
                }

                $processed[ $localizedKey ] = $localizedRule;
            }
        }

        return $processed;
    }


    /**
     * Returns the placeholder for translated rules that should be decorated.
     *
     * @return string
     */
    protected function getTranslatedRulePlaceHolder()
    {
        return TranslationLocaleHelper::VALIDATION_LOCALE_PLACEHOLDER;
    }

    /**
     * Returns all available translation locales.
     *
     * @return string[]
     */
    protected function getTranslationLocales()
    {
        /** @var TranslationLocaleHelperInterface $helper */
        $helper = app(TranslationLocaleHelperInterface::class);

        return $helper->availableLocales();
    }

}
