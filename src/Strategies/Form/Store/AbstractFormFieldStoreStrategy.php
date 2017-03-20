<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeValidationResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationValidationResolver;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Translation\TranslationLocaleHelper;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesSourceStrategies;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class AbstractFormFieldStoreStrategy implements FormFieldStoreStrategyInterface
{
    use ResolvesSourceStrategies;


    /**
     * @var ModelFormFieldDataInterface|ModelFormFieldData
     */
    protected $formFieldData;

    /**
     * @var array
     */
    protected $parameters = [];


    /**
     * Sets the relevant form field data to provide a context.
     *
     * @param ModelFormFieldDataInterface $data
     * @return $this
     */
    public function setFormFieldData(ModelFormFieldDataInterface $data)
    {
        $this->formFieldData = $data;

        return $this;
    }

    /**
     * Sets parameters to use for retrieving & storing.
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        if ($this->isTranslated()) {
            return $model->translations->pluck($source, config('translatable.locale_key', 'locale'))->toArray();
        }

        return $this->resolveModelSource($model, $source);
    }

    /**
     * Returns field value based on list parent key data.
     *
     * Only relevant for store strategies that may be used for fields that correspond to list parent relations.
     * May simply return null otherwise.
     *
     * @param string $key
     * @return mixed
     */
    public function valueForListParentKey($key)
    {
        return $key;
    }

    /**
     * Stores a submitted value on a model
     *
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    public function store(Model $model, $source, $value)
    {
        if ( ! $this->isTranslated()) {
            $this->performStore($model, $source, $value);
            return;
        }

        /** @var Model|Translatable $model */

        if ( ! is_array($value)) {
            throw new UnexpectedValueException("Value should be in per-locale array format for translatable data");
        }

        foreach ($value as $locale => $singleValue) {
            $this->performStore($model->translateOrNew($locale), $source, $singleValue);
        }
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        if (method_exists($model, $source)) {
            $model->{$source}($value);
            return;
        }

        $model->{$source} = $value;
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function storeAfter(Model $model, $source, $value)
    {
        if ( ! $this->isTranslated()) {
            $this->performStoreAfter($model, $source, $value);
            return;
        }

        /** @var Model|Translatable $model */

        if ( ! is_array($value)) {
            throw new UnexpectedValueException("Value should be in per-locale array format for translatable data");
        }

        foreach ($value as $locale => $singleValue) {
            $this->performStoreAfter($model->translateOrNew($locale), $source, $singleValue);
        }
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function performStoreAfter(Model $model, $source, $value)
    {
    }

    /**
     * Returns validation rules to use for submitted form data for this strategy.
     *
     * If the return array is associative, rules are expected nested per key,
     * otherwise the rules will be added to the top level key.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData|null $field
     * @param ModelInformationInterface|ModelInformation|null     $modelInformation
     * @param bool                                                $create
     * @return array|false false if no validation should be performed.
     */
    public function validationRules(
        ModelFormFieldDataInterface $field = null,
        ModelInformationInterface $modelInformation = null,
        $create
    ) {
        // Default behavior is to check for attribute/relation validation rules
        $rules = $this->getStrategySpecificRules($field);

        // Fallback behavior is to check for attribute/relation validation rules
        $modelRules = $this->getModelInformationBasedRules($field, $modelInformation);

        // Merge the rules together sensibly
        $rules = $this->mergeValidationRules($rules, $modelRules);

        $key = $field->key();

        // Translations are handled with locale-keyed associative arrays, using placeholders
        if ($rules && $field->translated()) {

            $placeholder = TranslationLocaleHelper::VALIDATION_LOCALE_PLACEHOLDER;

            // Modify and overwrite required rule, if present to special locale-context required rule
            if (is_string($rules)) {
                if ($rules === 'required') {
                    $rules = $this->getTranslationRequiredWithRule($modelInformation, $key);
                }
            } elseif ($index = array_search('required', $rules)) {
                $rules[$index] = $this->getTranslationRequiredWithRule($modelInformation, $key);
            }

            $rules = [
                $key                      => 'array',
                $key . '.' . $placeholder => $rules
            ];
        }

        return $rules;
    }

    /**
     * Returns required with rule for translated attributes.
     *
     * This collects all, except the indicated, attribute/field keys and
     * combines them into a single 'required_with' validation rule, with
     * a locale placeholder.
     *
     * @param ModelInformationInterface|ModelInformation $info
     * @param string|null                                $key field/attribute key
     * @return string
     */
    protected function getTranslationRequiredWithRule(ModelInformationInterface $info, $key = null)
    {
        $translated = array_keys(
            array_filter(
                $info->attributes,
                function (ModelAttributeDataInterface $attribute) use ($key) {
                    /** @var ModelAttributeData $attribute */

                    if (null !== $key && $key == $attribute->name) {
                        return false;
                    }

                    return $attribute->translated;
                }
            )
        );

        if ( ! count($translated)) {
            return '';
        }

        $translated = array_map(
            function ($key) {
                return $key . '.<trans>';
            },
            $translated
        );

        return 'required_with:' . implode(',', $translated);
    }

    /**
     * Returns validation rules specific for the strategy.
     *
     * Override this to set validation rules that should override attribute- or
     * relation-based validation rules.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return array|false|null     null to fall back to default rules.
     */
    protected function getStrategySpecificRules(ModelFormFieldDataInterface $field = null)
    {
        return null;
    }

    /**
     * Returns validation rules based on model information.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData|null $field
     * @param ModelInformationInterface|ModelInformation|null     $modelInformation
     * @return array|false
     */
    protected function getModelInformationBasedRules(
        ModelFormFieldDataInterface $field = null,
        ModelInformationInterface $modelInformation = null
    ) {
        if ( ! $field || ! $modelInformation) {
            return false;
        }

        $key = $field->key();

        if (array_key_exists($key, $modelInformation->attributes)) {

            return $this->getAttributeValidationResolver()->determineValidationRules(
                $modelInformation->attributes[ $key ],
                $field
            );

        }

        if (array_key_exists($key, $modelInformation->relations)) {

            return $this->getRelationValidationResolver()->determineValidationRules(
                $modelInformation->relations[ $key ],
                $field
            );
        }

        return false;
    }

    /**
     * Sensibly merges validation rules set specifically for the model and determined by model information.
     *
     * @param array|string|false $specificRules
     * @param array|string|false $modelRules
     * @return array|string|false
     */
    protected function mergeValidationRules($specificRules, $modelRules)
    {
        if (empty($specificRules)) {
            return $modelRules;
        }

        if (empty($modelRules)) {
            return $specificRules;
        }

        $specificRules = is_array($specificRules) ? $specificRules : [ $specificRules ];
        $modelRules    = is_array($modelRules)    ? $modelRules    : [ $modelRules ];

        $flippedInheritable = array_flip($this->inheritableRules());

        // Detect if any of the specific rules are nested, in which case the normal merging process should be skipped.
        // Though it is technically possible that these nested properties will match an attribute directly,
        // this should not be assumed -- configure validation rules manually for the best results.
        if (count(array_filter($specificRules, 'is_array'))) {
            return $specificRules;
        }

        // Remove rules that may not be inherited, because present in specific rules
        array_forget($flippedInheritable, array_map([ $this, 'getRuleType' ], $specificRules));

        foreach ($modelRules as $modelRule) {

            $ruleType = $this->getRuleType($modelRule);

            if ( ! array_key_exists($ruleType, $flippedInheritable)) {
                continue;
            }

            $specificRules[] = $modelRule;
        }

        return $specificRules;
    }

    /**
     * Returns validation rules that may be inherited from model information data,
     * to be included into specific strategy rules if not already present.
     *
     * @return array
     */
    protected function inheritableRules()
    {
        return [
            'required',
            'filled',
            'nullable',
            'unique',
            'exists',
        ];
    }

    /**
     * Returns a rule type for a given validation rule.
     *
     * @param $rule
     * @return string
     */
    protected function getRuleType($rule)
    {
        if (false === ($pos = strpos($rule, ':'))) {
            return $rule;
        }

        return substr($rule, 0, $pos);
    }


    /**
     * Adjusts or normalizes a value before storing it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        if ($this->isNullable() && empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * @return bool
     */
    protected function isTranslated()
    {
        return in_array('translated', $this->parameters);
    }

    /**
     * @return bool
     */
    protected function isNullable()
    {
        return in_array('nullable', $this->parameters);
    }

    /**
     * @param Model $model
     * @return ModelInformation|false
     */
    protected function getModelInformation(Model $model)
    {
        return $this->getModelInformationRepository()->getByModel($model);
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

    /**
     * @return AttributeValidationResolver
     */
    protected function getAttributeValidationResolver()
    {
        return app(AttributeValidationResolver::class);
    }

    /**
     * @return RelationValidationResolver
     */
    protected function getRelationValidationResolver()
    {
        return app(RelationValidationResolver::class);
    }
}
