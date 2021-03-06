<?php
namespace Czim\CmsModels\Support\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleMergerInterface;
use Illuminate\Support\Collection;

/**
 * Class ValidationRuleMerger
 *
 * Merges validation rules for a field, given the strategy-based rules,
 * the attribute information and the validation rules configured for the model.
 */
class ValidationRuleMerger implements ValidationRuleMergerInterface
{

    /**
     * Merges model configuration rules for the shared section with the create or update section.
     *
     * This results in user-specified validation rules, which may be further enriched.
     *
     * @param array           $shared       shared validation rules
     * @param array|null|bool $specific     specific validation rules for create or update
     * @param bool            $replace      whether we're replacing or merging the rulesets
     * @return array
     */
    public function mergeSharedConfiguredRulesWithCreateOrUpdate($shared, $specific, $replace = false)
    {
        // If specific is flagged false, then base rules should be ignored.
        if ($specific === false) {
            return [];
        }

        // Otherwise, make sure the rules are merged as arrays
        if ($specific === null || $specific === true) {
            $specific = [];
        }

        // In replace mode, rules should be merged in from shared only per key, if present as value.
        // When shared rules are merged in specifically like this, their 'value-only' key marker is
        // replaced by the actual key-value pair from the shared rules.
        if ($replace) {

            $sharedKeys = array_filter(
                $specific,
                function ($value, $key) {
                    return is_string($value) && is_numeric($key);
                },
                ARRAY_FILTER_USE_BOTH
            );

            // After this, there may still be string values in the array that do not have (non-numeric)
            // keys. These are explicit inclusions of form-field rules.
            $specific = array_filter(
                $specific,
                function ($value, $key) use ($shared) {
                    return ! is_string($value) || ! is_numeric($key) || ! array_key_exists($value, $shared);
                },
                ARRAY_FILTER_USE_BOTH
            );

            return array_merge(
                array_only($shared, $sharedKeys),
                $specific
            );
        }

        return array_merge(
            $shared ?: [],
            $specific
        );
    }

    /**
     * Takes any duplicate presence of a key() in a set of rules and
     * collapses the rules into a single entry per key.
     *
     * @param ValidationRuleDataInterface[] $rules
     * @return ValidationRuleDataInterface[]
     */
    public function collapseRulesForDuplicateKeys(array $rules)
    {
        $collection = (new Collection($rules))
            // Group by key so we can process each key separately
            ->groupBy(function (ValidationRuleDataInterface $rule) {
                return $rule->key() ?: '';
            })
            // Collapse by merging separate rules into the first data item in the list, per key.
            ->transform(function (Collection $rules) {
                /** @var ValidationRuleDataInterface $data */
                /** @var Collection|ValidationRuleDataInterface[] $rules */
                $data = $rules->shift();

                foreach ($rules as $rule) {
                    $data->setRules(array_merge($data->rules(), $rule->rules()));
                }

                return $data;
            });

        $array = [];

        foreach ($collection as $rule) {
            $array[] = $rule;
        }

        return $array;
    }

    /**
     * Merges together the validation rules defined for a strategy,
     * with rules defined for a form field's attribute (or relation).
     *
     * Before passing arrays of rule objects to this class,
     * they must be normalized and collapsed per key.
     *
     * @param ValidationRuleDataInterface[] $strategyRules
     * @param ValidationRuleDataInterface[] $attributeRules
     * @return ValidationRuleDataInterface[]
     */
    public function mergeStrategyAndAttributeBased(array $strategyRules, array $attributeRules)
    {
        if (empty($strategyRules)) {
            return $attributeRules;
        }

        if (empty($attributeRules)) {
            return $strategyRules;
        }

        // Detect if any of the specific rules are nested, in which case the normal merging process should be skipped.
        // Though it is technically possible that these nested properties will match an attribute directly,
        // this should not be assumed -- configure validation rules manually for the best results.

        if ($this->rulesArrayContainsAnyNestedRules($strategyRules)) {
            return $strategyRules;
        }

        $attributeRules = new Collection($attributeRules);

        /** @var ValidationRuleDataInterface[]|Collection $attributeRules */
        $attributeRules = $attributeRules->keyBy(
            function (ValidationRuleDataInterface $rule) { return $rule->key(); }
        );

        // Todo: This needs to be cleaned up and ready to deal with much more complicated
        //       scenario's than will currently occur. Right now the following are not
        //       taken into account:
        //          - localeIndex, translated, and requiredWith translated differences
        // For now, the strategy rules are kept mostly as-is.

        foreach ($strategyRules as &$rule) {

            if ( ! $attributeRules->has($rule->key())) {
                continue;
            }

            $rule = $this->mergeIndividualStrategyAndAttributeBasedRule($rule, $attributeRules->get($rule->key()));
        }
        unset ($rule);

        return $strategyRules;
    }


    /**
     * Updates a list of validation rules to make required fields work in a per-locale context.
     *
     * The challenge here is to prevent a required translated field to be required *for all
     * available locales* -- instead requiring it only if *anything* for that locale is
     * entered.
     *
     * @param ValidationRuleDataInterface[] $rules
     * @return ValidationRuleDataInterface[]
     */
    public function convertRequiredForTranslatedFields(array $rules)
    {
        $isTranslatedKeys = [];  // list of rule keys that are translated
        $hasRequiredKeys  = [];  // list of rule keys that are translated and 'required'

        foreach ($rules as $index => $rule) {

            if ( ! $rule->isTranslated()) {
                continue;
            }

            $isTranslatedKeys[] = $rule->key() ?: '';

            if (in_array('required', $rule->rules())) {
                $hasRequiredKeys[ $index ] = $rule->key() ?: '';
            }
        }

        // For each required translated rule,
        // get all the keys for all other translated field rules (except itself)
        // and inject them into a required_with rule, that replaces the required rule.

        foreach ($hasRequiredKeys as $index => $key) {

            $rule = $rules[ $index ];

            $this->replaceRequiredForRequiredWith($rule, array_diff($isTranslatedKeys, [ $key ]));
        }

        return $rules;
    }

    /**
     * Updates the rules in a validation data object to replace the required rule with
     * a required_with rule for multiple fields.
     *
     * @param ValidationRuleDataInterface $rule
     * @param array                       $requiredWithKeys
     */
    protected function replaceRequiredForRequiredWith(ValidationRuleDataInterface $rule, array $requiredWithKeys)
    {
        $rules = array_diff($rule->rules(), ['required']);

        // If there are no required_with keys to replace it with, leave the rule out
        if (count($requiredWithKeys)) {

            $rules[] = 'required_with:' . implode(',', $requiredWithKeys);
        }

        $rule->setRules($rules);
    }


    /**
     * @param ValidationRuleDataInterface $strategyRule
     * @param ValidationRuleDataInterface $attributeRule
     * @return ValidationRuleDataInterface
     */
    protected function mergeIndividualStrategyAndAttributeBasedRule(
        ValidationRuleDataInterface $strategyRule,
        ValidationRuleDataInterface $attributeRule
    ) {
        $strategyRules     = $strategyRule->rules();
        $strategyRuleTypes = array_map([$this, 'getRuleType'], $strategyRules);

        // Find the inheritable rules that may be merged if found
        $inheritable = array_diff($this->inheritableRules(), $strategyRuleTypes);

        foreach ($attributeRule->rules() as $rule) {

            if (in_array($this->getRuleType($rule), $inheritable)) {
                $strategyRules[] = $rule;
            }
        }

        return $strategyRule->setRules($strategyRules);
    }

    /**
     * Validation rule data instances with (any) nonempty keys have nested rules.
     *
     * @param ValidationRuleDataInterface[] $rules
     * @return bool
     */
    protected function rulesArrayContainsAnyNestedRules(array $rules)
    {
        foreach ($rules as $rule) {

            if ( ! empty($rule->key())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a rule type for a given validation rule.
     *
     * @param string $rule
     * @return string
     */
    protected function getRuleType($rule)
    {
        if ( ! is_string($rule)) {
            return '';
        }

        if (false === ($pos = strpos($rule, ':'))) {
            return $rule;
        }

        return substr($rule, 0, $pos);
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

}
