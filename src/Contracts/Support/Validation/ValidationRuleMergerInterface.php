<?php
namespace Czim\CmsModels\Contracts\Support\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;

interface ValidationRuleMergerInterface
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
    public function mergeSharedConfiguredRulesWithCreateOrUpdate($shared, $specific, $replace = false);

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
