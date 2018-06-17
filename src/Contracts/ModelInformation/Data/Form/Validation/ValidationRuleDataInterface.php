<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation;

use Illuminate\Contracts\Support\Arrayable;

interface ValidationRuleDataInterface extends Arrayable
{
    /**
     * Sets the key for the validation rules in dot notation.
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key);

    /**
     * Returns the key in dot notation.
     *
     * @return string
     */
    public function key();

    /**
     * Sets whether the field is translated.
     *
     * @param bool $translated
     * @return $this
     */
    public function setIsTranslated($translated = true);

    /**
     * Returns whether the field is known to be translated.
     *
     * @return bool
     */
    public function isTranslated();

    /**
     * Sets the context to be required_with for translations.
     *
     * @param bool $required
     * @return $this
     */
    public function setRequiredWithTranslation($required = true);

    /**
     * @return bool
     */
    public function isRequiredWithTranslation();

    /**
     * Sets validation rules.
     *
     * @param array|string[] $rules
     * @return $this
     */
    public function setRules(array $rules);

    /**
     * Returns the validation rules.
     *
     * @return array|string[]
     */
    public function rules();

}