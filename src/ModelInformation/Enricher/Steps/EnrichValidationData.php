<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleMergerInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeValidationResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationValidationResolver;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\Form\Validation\ValidationRuleData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesFormStoreStrategies;
use Exception;
use UnexpectedValueException;

class EnrichValidationData extends AbstractEnricherStep
{
    use ResolvesFormStoreStrategies;

    /**
     * This key, in array validation rule configuration, identifies
     * a special data set that should be interpreted as a validation
     * rule data object with various properties. It serves to identify
     * it as opposed to 'normal' arrays with validation rules.
     *
     * @var string
     */
    const VALIDATION_RULE_DATA_KEY = '**';


    /**
     * Mapping of CMS generated rule keys per form field.
     *
     * Set while collecting generated rules from form fields,
     * and used to determine which rules should be replaced,
     * overwritten or ignored.
     *
     * @var array   associative, list of rule keys, keyed by form field key
     */
    protected $generatedRulesMap = [];

    /**
     * The fields present in the layout for the form.
     *
     * @var array
     */
    protected $layoutFields = [];


    /**
     * Performs enrichment of validation rules based on form field strategies.
     *
     * @throws ModelInformationEnrichmentException
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->form->fields)) {
            return;
        }

        $this->layoutFields = $this->info->form->getLayoutFormFieldKeys();

        $this
            ->enrichCreateRules()
            ->enrichUpdateRules();
    }

    /**

     * @throws ModelInformationEnrichmentException
     */
    protected function enrichCreateRules()
    {
        $customRules     = $this->mergeConfiguredSharedRulesWithSpecific(true);
        $generatedRules  = $this->getFormFieldGeneratedRules(true);

        $excludeKeys     = $this->getCustomizedRuleKeysToExclude($customRules);
        $keepDefaultKeys = $this->getCustomizedRuleKeysToKeepDefault($customRules);

        $customRules = $this->normalizeValidationRuleSourceDataFromConfig($customRules);

        $replace = (bool) $this->info->form->validation->create_replace;

        $this->info->form->validation->create = $this->enrichRulesNew(
            $customRules,
            $generatedRules,
            $replace,
            $excludeKeys,
            $keepDefaultKeys,
            $this->generatedRulesMap
        );

        return $this;
    }

    /**
     * @return EnrichValidationData
     * @throws ModelInformationEnrichmentException
     */
    protected function enrichUpdateRules()
    {
        $customRules     = $this->mergeConfiguredSharedRulesWithSpecific(false);
        $generatedRules  = $this->getFormFieldGeneratedRules(false);

        $excludeKeys     = $this->getCustomizedRuleKeysToExclude($customRules);
        $keepDefaultKeys = $this->getCustomizedRuleKeysToKeepDefault($customRules);

        $customRules = $this->normalizeValidationRuleSourceDataFromConfig($customRules);

        $replace = (bool) $this->info->form->validation->update_replace;

        $this->info->form->validation->update = $this->enrichRulesNew(
            $customRules,
            $generatedRules,
            $replace,
            $excludeKeys,
            $keepDefaultKeys,
            $this->generatedRulesMap
        );

        return $this;
    }

    /**
     * Returns keys for rules that should not be included by custom configuration.
     *
     * @param array $customRules
     * @return string[]
     */
    protected function getCustomizedRuleKeysToExclude(array $customRules)
    {
        $keys = [];

        foreach ($customRules as $key => $ruleParts) {

            // If the set value for this key is false/null, the rule must be ignored entirely.
            // This will disable enrichment using form field rules.
            if (false === $ruleParts || empty($ruleParts)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }


    /**
     * Returns keys for rules that should be kept from the default generated rules,
     * even when in replace mode.
     *
     * @param array $customRules
     * @return string[]
     */
    protected function getCustomizedRuleKeysToKeepDefault(array $customRules)
    {
        $keys = [];

        foreach ($customRules as $key => $ruleParts) {

            // If the configured value is the name of a key (and so it is a non-associative
            // part of the rules), it indicates that the default should be kept/copied over,
            // for the key the value denotes.
            if (is_string($ruleParts) && is_numeric($key)) {
                $keys[] = $ruleParts;
            }
        }

        return $keys;
    }

    /**
     * Enriches rules and returns the enriched rules array.
     *
     * @param array    $customRules         rules custom-defined in the configuration, normalized
     * @param array    $generatedRules      rules generated by the CMS, normalized
     * @param bool     $replace             whether to replace rules entirely (and not enricht non-present rules)
     * @param string[] $excludeKeys         keys for rules that should be excluded
     * @param string[] $keepDefaultKeys     keys for rules that should be kept as generated by default
     * @param array[]  $generatedRulesMap   the mapped rules per form field for generated rules
     * @return array
     */
    protected function enrichRulesNew(
        array $customRules,
        array $generatedRules,
        $replace,
        array $excludeKeys,
        array $keepDefaultKeys,
        array $generatedRulesMap
    ) {
        // so use them as a starting point.
        // If custom rules are configured, they should not overwritten by form field rules,
        $result = $customRules;

        // Include any generated rules explicitly marked for inclusion.
        foreach ($keepDefaultKeys as $key) {
            if ( ! array_key_exists($key, $generatedRules)) {
                continue;
            }

            $result[ $key ] = $generatedRules[ $key ];
        }

        // If the generated rules are explicitly replaced default rules, we don't have to merge them.
        // Otherwise, append any form field defined rule that is
        // not yet included and not marked for exclusion.
        if ( ! $replace) {

            foreach ($generatedRulesMap as $fieldKey => $ruleKeys) {

                // The field key or any of its nested child rules keys may be disabled.
                // If the field key itself is excluded, find the mapped keys for that
                // field and exclude any of them.

                // So skip any set of rules belonging to a field whose key has
                // been excluded (as a rule key).
                if (in_array($fieldKey, $excludeKeys)) {
                    continue;
                }

                foreach ($ruleKeys as $key) {

                    // Skip if ..
                    if (
                        // .. the key is already included, or marked for exclusion
                            in_array($key, $excludeKeys)
                        ||  in_array($key, $keepDefaultKeys)
                        // .. the result array already has an entry for the key
                        ||  array_key_exists($key, $result)
                        // .. no generated rule exists for the key (safeguard)
                        ||  ! array_key_exists($key, $generatedRules)
                    ) {
                        continue;
                    }

                    $result[ $key ] = $generatedRules[ $key ];
                }
            }
        }

        return $this->castValidationRulesToArray($result);
    }

    /**
     * Note that this does not normalize the rules to data objects, since the
     * configuration format is semantic (in ways to nullify or default rules).
     *
     * @param bool $forCreate   whether merging is for the 'create' section
     * @return array
     */
    protected function mergeConfiguredSharedRulesWithSpecific($forCreate = false)
    {
        $type = $forCreate ? 'create' : 'update';

        $replace  = (bool) $this->info->form->validation->{$type . '_replace'};
        $specific = $this->info->form->validation->{$type};

        return $this->getRuleMerger()->mergeSharedConfiguredRulesWithCreateOrUpdate(
            $this->info->form->validation->sharedRules(),
            $specific,
            $replace
        );
    }

    /**
     * Returns rules determined by form field strategies.
     *
     * @param bool $forCreate
     * @return array
     * @throws ModelInformationEnrichmentException
     */
    protected function getFormFieldGeneratedRules($forCreate = true)
    {
        $this->generatedRulesMap = [];

        $rules = [];

        foreach ($this->info->form->fields as $field) {

            try {
                $this->getFormFieldGeneratedRule($field, $forCreate, $rules);

            } catch (Exception $e) {

                $section = 'form.validation.' . ($forCreate ? 'create' : 'update');

                // Wrap and decorate exceptions so it is easier to track the problem source
                throw (new ModelInformationEnrichmentException(
                    "Issue with validation rules for form field '{$field->key()}' ({$section}): \n{$e->getMessage()}",
                    $e->getCode(),
                    $e
                ))
                    ->setSection($section)
                    ->setKey($field->key());
            }
        }

        return $rules;
    }

    /**
     * Updates collected rules array with rules based on form field data.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @param bool                                           $forCreate
     * @param array                                          $rules     by reference
     */
    protected function getFormFieldGeneratedRule(ModelFormFieldDataInterface $field, $forCreate, array &$rules)
    {
        $this->generatedRulesMap[ $field->key() ] = [];

        if ( ! $this->isFormFieldRelevant($field, $forCreate)) {
            return;
        }

        // The rules returned by a strategy (specific), and those provided by the
        // field type (attribute/relation), must first be combined --
        // and in preparation for that be cast as uniform arrays of ValidationRuleData instances.

        $fieldRules = $this->getAndMergeFormFieldRulesForStrategyAndBasedOnModelInformation($field, $forCreate);

        // At this point we have a normalized array of data objects,
        // which has dot-notation keys (for array-nested rules) that is offset
        // for the field (so it leaves out the field key itself).
        // Rules for the field key itself will thus have empty/null keys.

        // The rules will also have been collapsed to one per key.

        foreach ($fieldRules as $rule) {

            // If the field is translated, mark the rules for it
            if ($field->translated()) {
                $rule->setIsTranslated();
            }

            if (empty($rule->key())) {
                $rule->setKey($field->key());
                $rules[ $field->key() ] = $rule;
                $this->generatedRulesMap[ $field->key() ][] = $field->key();
                continue;
            }

            $rule->prefixKey($field->key());

            $rules[ $rule->key() ] = $rule;
            $this->generatedRulesMap[ $field->key() ][] = $rule->key();
        }
    }

    /**
     * @param ModelFormFieldDataInterface $field
     * @param bool                        $forCreate
     * @return ValidationRuleDataInterface[]
     */
    protected function getAndMergeFormFieldRulesForStrategyAndBasedOnModelInformation(
        ModelFormFieldDataInterface $field,
        $forCreate = false
    ) {
        // Get and normalize the rules to be merged
        $strategyRules = $this->getFormFieldRulesForStoreStrategy($field, $forCreate);
        $baseRules     = $this->getFormFieldModelInformationBasedRules($field);

        $strategyRules = $this->normalizeValidationRuleSourceData($strategyRules);
        $baseRules     = $this->normalizeValidationRuleSourceData($baseRules);

        return $this->getRuleMerger()->mergeStrategyAndAttributeBased($strategyRules, $baseRules);
    }

    /**
     * Normalizes custom configured rules data to include only specified rules.
     *
     * This leaves out rules nullified to exclude them, as well as rules
     * marked to be included/kept as generated by default.
     *
     * @param array $rules
     * @return ValidationRuleDataInterface[]
     */
    protected function normalizeValidationRuleSourceDataFromConfig(array $rules)
    {
        // Remove entries to be ignored and excluded
        $filteredRules = array_filter($rules, function ($rule, $key) {

            return  false !== $rule
                &&  ! empty($rule)
                &&  ! (is_string($rule) && is_numeric($key));

        }, ARRAY_FILTER_USE_BOTH);

        return $this->normalizeValidationRuleSourceData($filteredRules);
    }

    /**
     * Normalizes generated validation rule data to an array of data object instances.
     *
     * The entries will be collapsed to one per key, and the array will be keyed by the key.
     *
     * @param mixed $rules
     * @return ValidationRuleDataInterface[]
     */
    protected function normalizeValidationRuleSourceData($rules)
    {
        if (false === $rules || empty($rules)) {
            return [];
        }

        if (is_string($rules)) {
            $rules = (array) $rules;
        }

        if ( ! is_array($rules)) {
            throw new UnexpectedValueException("Form field base validation rules not array or arrayable");
        }

        // Loop through and cast anything not already a data object
        $rules = array_map([$this, 'normalizeRulesProperty'], $rules, array_keys($rules));

        // Make sure there is only one entry per rule key
        $rules = $this->getRuleMerger()->collapseRulesForDuplicateKeys($rules);

        // Key the array by the rule key
        return array_combine(
            array_map(function (ValidationRuleDataInterface $rule) {
                return $rule->key() ?: '';
            }, $rules),
            $rules
        );
    }

    /**
     * Normalizes rules data for a single validation rule key.
     *
     * @param mixed      $rules
     * @param int|string $key       The source array key
     * @return ValidationRuleDataInterface
     */
    protected function normalizeRulesProperty($rules, $key)
    {
        if ($rules instanceof ValidationRuleDataInterface) {
            return $rules;
        }

        if (is_string($rules)) {
            // Convert Laravel's pipe-syntax to array
            $rules = explode('|', $rules);
        }

        if ( ! is_array($rules)) {
            $rules = (array) $rules;
        }

        // If the array is marked up in a special format, it can reflect the
        // contents of a validation rule dataobject directly.
        if (is_array(array_get($rules, static::VALIDATION_RULE_DATA_KEY))) {

            return $this->makeValidationRuleDataFromSpecialArraySyntax($rules, $key);
        }

        // If the array is associative (and the key is non-numeric), included
        // it as a nested postfix for the field key, by including it in the data.
        return new ValidationRuleData($rules, is_numeric($key) ? null : $key);
    }

    /**
     * Casts an array of validation rule data objects to array-only format.
     *
     * The rules array argument must be collapsed to one per key.
     *
     * @param ValidationRuleDataInterface[] $rules
     * @return array
     */
    protected function castValidationRulesToArray(array $rules)
    {
        return array_map(function (ValidationRuleDataInterface $rule) {
            return $rule->rules();
        }, $rules);
    }

    /**
     * Makes a validation rule data object from a special configuration array.
     *
     * @param array       $rules
     * @param null|string $key
     * @return ValidationRuleData
     */
    protected function makeValidationRuleDataFromSpecialArraySyntax(array $rules, $key)
    {
        $data = new ValidationRuleData(
            array_get($rules, 'rules'),
            array_get($rules, 'key', is_numeric($key) ? null : $key)
        );

        if (array_has($rules, 'translated')) {
            $data->setIsTranslated((bool) $rules['translated']);
        }

        if (array_has($rules, 'locale_index')) {

            if ((int) $rules['locale_index'] < 1) {
                throw new UnexpectedValueException(
                    "Locale index for configured validation data cannot be less than 1 (key/index: {$key})"
                );
            }

            $data->setLocaleIndex((int) $rules['locale_index']);
        }

        if (array_has($rules, 'required_with_translation')) {
            $data->setRequiredWithTranslation((bool) $rules['required_with_translation']);
        }

        return $data;
    }

    /**
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @param bool                                           $forCreate
     * @return array|false
     */
    protected function getFormFieldRulesForStoreStrategy(ModelFormFieldDataInterface $field, $forCreate = false)
    {
        if ( ! $field->store_strategy) {
            return [];
        }

        $instance = $this->getFormFieldStoreStrategyInstanceForField($field);

        $instance->setFormFieldData($field);
        $instance->setParameters(
            $this->getFormFieldStoreStrategyParametersForField($field)
        );

        return $instance->validationRules($this->info, $forCreate);
    }

    /**
     * @param ModelFormFieldDataInterface $field
     * @param bool                        $forCreate
     * @return bool
     */
    protected function isFormFieldRelevant(ModelFormFieldDataInterface $field, $forCreate = false)
    {
        return  ! ( $forCreate && ! $field->create()
                ||  ! $forCreate && ! $field->update()
                ||  ! $this->isFormFieldPresentInLayout($field->key())
                );
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function isFormFieldPresentInLayout($key)
    {
        return in_array($key, $this->layoutFields);
    }

    /**
     * Returns validation rules based on model information for a given form field.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData|null $field
     * @return array|false
     */
    protected function getFormFieldModelInformationBasedRules(ModelFormFieldDataInterface $field)
    {
        $key              = $field->key();
        $modelInformation = $this->getModelInformation();


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
     * Prefixes a dot-notation key with a string.
     *
     * @param string      $prefix
     * @param null|string $key
     * @return string
     */
    protected function prefixDotKey($prefix, $key)
    {
        if (empty($key)) {
            return $prefix;
        }

        return $prefix . '.' . $key;
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     * @codeCoverageIgnore
     */
    protected function getModelInformation()
    {
        return $this->info;
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

    /**
     * @return ValidationRuleMergerInterface
     */
    protected function getRuleMerger()
    {
        return app(ValidationRuleMergerInterface::class);
    }

}
