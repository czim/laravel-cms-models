<?php
namespace Czim\CmsModels\Support\Validation;

use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;
use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleDecoratorInterface;
use Czim\CmsModels\Support\Translation\TranslationLocaleHelper;
use Illuminate\Database\Eloquent\Model;

class ValidationRuleDecorator implements ValidationRuleDecoratorInterface
{

    /**
     * @var string
     */
    const MODEL_KEY_PLACEHOLDER = '<key>';

    /**
     * Rules being decorated.
     *
     * @var array
     */
    protected $rules;

    /**
     * Model being updated, if available.
     *
     * @var Model|null
     */
    protected $model;


    /**
     * Decorates given validation rules
     *
     * @param array      $rules
     * @param Model|null $model     if updating, the model being updated
     * @return array
     */
    public function decorate(array $rules, Model $model = null)
    {
        $this->rules = $rules;
        $this->model = $model;

        $this
            ->replaceTranslationPlaceholders()
            ->replaceKeyPlaceholders();

        return $this->rules;
    }

    /**
     * Decorates rules by replacing a model key value placeholder.
     *
     * @return $this
     */
    protected function replaceKeyPlaceholders()
    {
        if ( ! $this->model) {
            return $this;
        }

        $placeholder = static::MODEL_KEY_PLACEHOLDER;

        foreach ($this->rules as $key => &$ruleSet) {

            if (is_string($ruleSet)) {

                $ruleSet = str_replace($placeholder, $this->model->getKey(), $ruleSet);
                continue;
            }

            if (is_array($ruleSet)) {

                foreach ($ruleSet as &$rule) {
                    $rule = str_replace($placeholder, $this->model->getKey(), $rule);
                }

                unset($rule);
            }
        }

        unset($ruleSet);

        return $this;
    }

    /**
     * Decorates rules by replacing translation placeholders and create rules per locale.
     *
     * @return $this
     */
    protected function replaceTranslationPlaceholders()
    {
        $locales     = $this->getTranslationLocales();
        $placeholder = $this->getTranslatedRulePlaceHolder();
        $processed   = [];

        // Detect and duplicate special rules for translated locales
        foreach ($this->rules as $key => $ruleSet) {

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

        $this->rules = $processed;

        return $this;
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
