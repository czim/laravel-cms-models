<?php
namespace Czim\CmsModels\Contracts\Support\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;

interface ValidationRuleMergerInterface
{

    /**
     * Takes any duplicate presence of a key() in a set of rules and
     * collapses the rules into a single entry per key.
     *
     * @param ValidationRuleDataInterface[] $rules
     * @return ValidationRuleDataInterface[]
     */
    public function collapseRulesForDuplicateKeys(array $rules);

    /**
     * Merges together the validation rules defined for a strategy with rules defined
     * for a form field's attribute (or relation).
     *
     * @param ValidationRuleDataInterface[] $strategyRules
     * @param ValidationRuleDataInterface[] $attributeRules
     * @return ValidationRuleDataInterface[]
     */
    public function mergeStrategyAndAttributeBased(array $strategyRules, array $attributeRules);

}
