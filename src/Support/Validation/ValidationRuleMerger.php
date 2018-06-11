<?php
namespace Czim\CmsModels\Support\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleCollectionInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleMergerInterface;
use Czim\CmsModels\ModelInformation\Data\Form\Validation\ValidationRuleCollection;
use Czim\CmsModels\ModelInformation\Data\Form\Validation\ValidationRuleData;

/**
 * Class ValidationRuleMerger
 *
 * Merges validation rules for a field, given the strategy-based rules,
 * the attribute information and the validation rules configured for the model.
 */
class ValidationRuleMerger implements ValidationRuleMergerInterface
{

    /**
     * Merges together the validation rules defined for a strategy with rules defined
     * for a form field's attribute (or relation).
     *
     * @param array|ValidationRuleDataInterface[]|string[] $strategyRules
     * @param array|ValidationRuleDataInterface[]|string[] $attributeRules
     * @return ValidationRuleCollectionInterface|ValidationRuleDataInterface[]
     */
    public function mergeStrategyAndAttributeBased(array $strategyRules, array $attributeRules)
    {
        $mergedRules = $this->mergeValidationRules($strategyRules, $attributeRules);

        $collection = new ValidationRuleCollection;

        foreach ($mergedRules as $key => $ruleData) {

            // If the date is already cast as a rule data object,
            // the key is embedded and we can ignore the index.
            if ($ruleData instanceof ValidationRuleDataInterface) {
                $collection->push($ruleData);
                continue;
            }

            if ( ! is_array($ruleData)) {
                $ruleData = [ $ruleData ];
            }

            // We can cast the rules into a rule data object, but since we do not know
            // the full key or translation status for the field, only partial data is set.

            // Since the data itself does not hold the key, the index is expected to be the
            // partial key, as children of the as yet unsupplied field key. Decoration
            // with the field key must prefix this before the key set here.

            // However, if the provided array is non-associative, all the provided
            // rules are for the field class -- and in that case, no keys should be
            // set here (since all rules should be decorated with ONLY the field key later).
            $postFixKey = is_numeric($key) ? null : $key;

            $collection->push(
                new ValidationRuleData($ruleData, $postFixKey)
            );
        }

        return $collection;
    }


    /**
     * Sensibly merges validation rules set specifically for a strategy and those
     * determined by model information.
     *
     * @todo this needs to be able to safely deal with validationruledata instances...
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

        // The rules are nested if they are an associative array,
        // or validationruledata instances with (any) nonempty keys.
        // todo

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
