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
     * Merges together the validation rules defined for a strategy with rules defined
     * for a form field's attribute (or relation).
     *
     * Before passing arrays of rule objects to this class, they must be normalized
     * and collapsed per key.
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

        // todo
        return $strategyRules;


        // Todo: This needs to be cleaned up and ready to deal with much more complicated
        //       scenario's than will currently occur. Right now the following are not
        //       taken into account:
        //          - localeIndex, translated, and requiredWith translated differences

        $groupedStrategyRules = (new Collection($strategyRules))
            ->groupBy(function (ValidationRuleDataInterface $rule) {
                return $rule->key() ?: '';
            });

        $groupedAttributeRules = (new Collection($attributeRules))
            ->groupBy(function (ValidationRuleDataInterface $rule) {
                return $rule->key() ?: '';
            });

        // Make a list of inheritable rules that do not appear in the strategy rules.
        // See if those rules appear in any of the attribute rules.
        // If they do, add those rules (but only those specifically) to the strategy rules.

        //foreach ($groupedStrategyRules as $key => $strategyRules) {
        //
        //    $combinedRules = $strategyRules;
        //
        //    // Remove rules that may not be inherited, because present in specific rules.
        //    $flippedInheritable = array_flip($this->inheritableRules());
        //
        //    // List inheritable rules that do not appear in the strategy-specific rules.
        //    array_forget($flippedInheritable, array_map([$this, 'getRuleType'], $combinedRules));
        //
        //
        //}

        //$combinedRules = $strategyRules;
        //
        //foreach ($attributeRules as $rule) {
        //
        //    $ruleType = $this->getRuleType($rule);
        //
        //    if ( ! array_key_exists($ruleType, $flippedInheritable)) {
        //        continue;
        //    }
        //
        //    $combinedRules[] = $rule;
        //}
        //
        //return $combinedRules;
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
     * @param ValidationRuleDataInterface $rule
     * @return string
     */
    protected function getRuleType(ValidationRuleDataInterface $rule)
    {
        if (false === ($pos = strpos($rule->rules(), ':'))) {
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
