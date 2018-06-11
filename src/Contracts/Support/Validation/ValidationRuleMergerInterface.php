<?php
namespace Czim\CmsModels\Contracts\Support\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleCollectionInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;

interface ValidationRuleMergerInterface
{

    /**
     * Merges together the validation rules defined for a strategy with rules defined
     * for a form field's attribute (or relation).
     * @param array|ValidationRuleDataInterface[]|string[] $strategyRules
     * @param array|ValidationRuleDataInterface[]|string[] $attributeRules
     * @return ValidationRuleCollectionInterface|ValidationRuleDataInterface[]
     */
    public function mergeStrategyAndAttributeBased(array $strategyRules, array $attributeRules);

}
