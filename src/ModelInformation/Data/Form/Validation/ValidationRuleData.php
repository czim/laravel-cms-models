<?php
namespace Czim\CmsModels\ModelInformation\Data\Form\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\Support\Translation\TranslationLocaleHelper;

/**
 * Class ValidationRuleData
 *
 * Data describing validation rule(s) for a single form field (key).
 */
class ValidationRuleData implements ValidationRuleDataInterface
{
    /**
     * String key that identifies a validation rule data set.
     */
    const IDENTIFIER_KEY = '**';


    /**
     * The validation rules in Laravel array syntax.
     *
     * @var array|string[]
     */
    protected $rules;

    /**
     * The key for the validation rules in dot notation, if known.
     *
     * @var null|string
     */
    protected $key;

    /**
     * Whether the field for the rules is translated.
     *
     * @var bool
     */
    protected $translated;

    /**
     * The position of the locale placeholder in the dot notation key string.
     *
     * @var int
     */
    protected $localeIndex = 1;

    /**
     * The locale placeholder to use in the dot notation key, if not default.
     *
     * @var null|string
     */
    protected $localePlaceholder;

    /**
     * If true, the key should be required_with when used in translation context.
     *
     * @var bool
     */
    protected $requiredWithTranslation = false;


    /**
     * @param array       $rules        the rules for this key in Laravel array notation
     * @param string|null $key          the field key
     */
    public function __construct(array $rules, $key = null)
    {
        $this->rules = $rules;
        $this->key   = $key;
    }


    /**
     * Sets the key for the validation rules in dot notation.
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Returns the key in dot notation.
     *
     * @return string
     */
    public function key()
    {
        if ( ! $this->isTranslated()) {
            return $this->key;
        }

        $parts = explode('.', $this->key);

        array_splice($parts, $this->localeIndex, 0, $this->getLocalePlaceholder());

        return implode('.', $parts);
    }

    /**
     * Sets whether the field is translated.
     *
     * @param bool $translated
     * @return $this
     */
    public function setIsTranslated($translated = true)
    {
        $this->translated = (bool) $translated;

        return $this;
    }

    /**
     * Returns whether the field is known to be translated.
     *
     * @return bool
     */
    public function isTranslated()
    {
        return $this->translated;
    }

    /**
     * Sets the index at which the locale placeholder should be inserted for a full key.
     *
     * @param int $index
     * @return $this
     */
    public function setLocaleIndex($index)
    {
        $this->localeIndex = (int) $index;

        return $this;
    }

    /**
     * Returns the index at which the locale placeholder is inserted for a full key.
     *
     * @return int
     */
    public function localeIndex()
    {
        return $this->localeIndex;
    }

    /**
     * Sets the context to be required_with for translations.
     *
     * @param bool $required
     * @return $this
     */
    public function setRequiredWithTranslation($required = true)
    {
        $this->requiredWithTranslation = (bool) $required;

        return $this;
    }

    /**
     * Returns whether the context is set to be required_with for translations.
     *
     * @return bool
     */
    public function isRequiredWithTranslation()
    {
        return $this->requiredWithTranslation;
    }

    /**
     * Sets validation rules.
     *
     * @param array|string[] $rules
     * @return $this
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Returns the validation rules.
     *
     * @return array|string[]
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * @return array|string[]
     */
    public function toArray()
    {
        return [
            static::IDENTIFIER_KEY => [
                'key'                     => $this->key,
                'rules'                   => $this->rules,
                'localeIndex'             => $this->localeIndex,
                'requiredWithTranslation' => $this->requiredWithTranslation,
            ]
        ];
    }

    /**
     * Returns the placeholder to use in the dot notation field key.
     *
     * E.g.: '<trans>'
     *
     * @return string
     */
    protected function getLocalePlaceholder()
    {
        if ($this->localePlaceholder === null) {
            return $this->getDefaultLocalePlaceholder();
        }

        return $this->localePlaceholder;
    }

    /**
     * @return string
     */
    protected function getDefaultLocalePlaceholder()
    {
        return TranslationLocaleHelper::VALIDATION_LOCALE_PLACEHOLDER;
    }

}
