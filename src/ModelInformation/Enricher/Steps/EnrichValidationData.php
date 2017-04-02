<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesFormStoreStrategies;
use Exception;
use Illuminate\Support\Arr;

class EnrichValidationData extends AbstractEnricherStep
{
    use ResolvesFormStoreStrategies;


    /**
     * List of rules generated for the current context (create/update).
     *
     * @var array
     */
    protected $generatedRules = [];

    /**
     * Mapping of generated rules per form field.
     *
     * @var array   associative, list of rule keys, keyed by form field key
     */
    protected $generatedRulesMap = [];

    /**
     * @var array
     */
    protected $layoutFields = [];

    /**
     * Performs enrichment of validation rules based on form field strategies.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->form->fields)) {
            return;
        }

        $this->layoutFields = $this->info->form->getLayoutFormFieldKeys();

        $this->enrichCreateRules()
             ->enrichUpdateRules();
    }

    /**
     * Enriches validation rules for (default or) create form data.
     *
     * @return $this
     */
    protected function enrichCreateRules()
    {
        $this->generatedRulesMap = [];

        $rules = $this->mergeDefaultRulesWithSpecific($this->info->form->validation->create, true);

        $this->generatedRules = $this->getFormFieldBaseRules(true);

        if ( ! count($rules)) {
            $rules = $this->generatedRules;
        } else {
            $rules = $this->enrichRulesWithFormRules($rules, $this->generatedRules, $this->generatedRulesMap, true);
        }

        $this->info->form->validation->create = $rules;

        return $this;
    }

    /**
     * @return $this
     */
    protected function enrichUpdateRules()
    {
        $rules = $this->mergeDefaultRulesWithSpecific($this->info->form->validation->update);

        $this->generatedRules = $this->getFormFieldBaseRules(false);

        // If no specific update rules were defined, use the
        // enriched create rules as a starting point.

        if ( ! count($rules)) {
            $rules = $this->generatedRules;
        } else {
            $rules = $this->enrichRulesWithFormRules($rules, $this->generatedRules, $this->generatedRulesMap);
        }

        $this->info->form->validation->update = $rules;

        return $this;
    }

    /**
     * @param array|null|bool $specific
     * @param bool            $forCreate    whether merging is for the 'create' section
     * @return array
     */
    protected function mergeDefaultRulesWithSpecific($specific, $forCreate = false)
    {
        $replace = (bool) $this->info->form->validation->{($forCreate ? 'create' : 'update') . '_replace'};

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

            $sharedRules = $this->info->form->validation->sharedRules();

            // After this, there may still be string values in the array that do not have (non-numeric)
            // keys. These are explicit inclusions of form-field rules.
            $specific = array_filter(
                $specific,
                function ($value, $key) use ($sharedRules) {
                    return ! is_string($value) || ! is_numeric($key) || ! array_key_exists($value, $sharedRules);
                },
                ARRAY_FILTER_USE_BOTH
            );

            return array_merge(
                array_only($this->info->form->validation->sharedRules(), $sharedKeys),
                $specific
            );
        }

        return array_merge(
            $this->info->form->validation->sharedRules() ?: [],
            $specific
        );
    }

    /**
     * Enrich a given set of rules with form field data determined.
     *
     * @param array $rules          rules to be enriched
     * @param array $formRules      generated form field rules
     * @param array $rulesFieldMap  mapping for which rules were added by which field
     * @param bool  $forCreate      whether the enrichment is for the 'create' section
     * @return array
     */
    protected function enrichRulesWithFormRules(array $rules, array $formRules, array $rulesFieldMap, $forCreate = false)
    {
        // If rules are set, they should not overwritten by form field rules.
        // and unkeyed string values should be enriched.
        // But keys not present should not be enriched or included.

        $enrichedRules    = [];
        $disabledRuleKeys = [];

        $replace = (bool) $this->info->form->validation->{($forCreate ? 'create' : 'update') . '_replace'};

        foreach ($rules as $key => $ruleParts) {

            // If the set value for this key is false/null, the rule must be ignored entirely.
            // This will disable enrichment using form field rules.
            if (false === $ruleParts || empty($ruleParts)) {
                $disabledRuleKeys[] = $key;
                continue;
            }

            // Enrich keys without values if possible. Here, the value is the name of a key
            // and this key exists in the form rules array, it should be taken as-is from the field rules.
            // This option exists to allow a config to specify that a form field rule should be included as-is,
            // even while overriding others.
            if (is_string($ruleParts) && is_numeric($key)) {
                if (array_key_exists($ruleParts, $formRules)) {
                    $enrichedRules[ $ruleParts ] = $formRules[ $ruleParts ];
                }
                continue;
            }

            // The rule has a key and is neither disabled nor taken as-is.
            // Normalize the rule (make an array) and keep it as defined by the user.
            // This means that the form field rule for this key is ignored.

            if ( ! is_array($ruleParts)) {
                $ruleParts = explode('|', $ruleParts);
            }

            $enrichedRules[ $key ] = $ruleParts;
        }

        // If not explicitly replacing default rules, append any form field defined rule that is
        // not yet included, as long as it is not explicitly disabled.
        if ( ! $replace) {

            foreach ($rulesFieldMap as $fieldKey => $ruleKeys) {

                // The field key or any of the child rules keys may be disabled.
                if (in_array($fieldKey, $disabledRuleKeys) || array_key_exists($fieldKey, $enrichedRules)) {
                    continue;
                }

                foreach (array_diff($ruleKeys, $disabledRuleKeys) as $ruleKey) {

                    if (    ! array_key_exists($ruleKey, $formRules)
                        ||  ! count($formRules[ $ruleKey ])
                        ||  array_key_exists($ruleKey, $enrichedRules)
                    ) {
                        continue;
                    }

                    $enrichedRules[ $ruleKey ] = $formRules[ $ruleKey ];
                }
            }
        }

        return $enrichedRules;
    }

    /**
     * Returns rules determined by form field strategies.
     *
     * @param bool $forCreate
     * @return array
     * @throws ModelInformationEnrichmentException
     */
    protected function getFormFieldBaseRules($forCreate = true)
    {
        $this->generatedRulesMap = [];

        $rules = [];

        foreach ($this->info->form->fields as $field) {

            try {
                $this->getFormFieldBaseRule($field, $forCreate, $rules);

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
    protected function getFormFieldBaseRule(ModelFormFieldDataInterface $field, $forCreate, array &$rules)
    {
        $this->generatedRulesMap[ $field->key() ] = [];

        // Leave out fields that are not relevant (or not in the layout)
        if (    $forCreate && ! $field->create()
            ||  ! $forCreate && ! $field->update()
            ||  ! in_array($field->key(), $this->layoutFields)
        ) {
            return;
        }

        $instance = $this->getFormFieldStoreStrategyInstanceForField($field);

        $instance->setFormFieldData($field);
        $instance->setParameters(
            $this->getFormFieldStoreStrategyParametersForField($field)
        );

        $fieldRules = $instance->validationRules($field, $this->info, $forCreate);

        if (false === $fieldRules) {
            return;
        }

        if (Arr::isAssoc($fieldRules)) {

            foreach ($fieldRules as $key => $nestedFieldRules) {
                $rules[ $key ] = $nestedFieldRules;
                $this->generatedRulesMap[ $field->key() ][] = $key;
            }

        } else {

            $rules[ $field->key() ] = $fieldRules;
            $this->generatedRulesMap[ $field->key() ][] = $field->key();
        }
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     */
    protected function getModelInformation()
    {
        return $this->info;
    }

}
