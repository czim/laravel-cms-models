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
     * Returns the key in dot notation, with locale placeholder where relevant.
     *
     * @return string
     */
    public function key();

    /**
     * Prefixes the currently set key with a dot-notation parent.
     *
     * The (final) dot (.) should not be included in the prefix string.
     *
     * @param string $prefix
     * @return $this
     */
    public function prefixKey($prefix);

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
