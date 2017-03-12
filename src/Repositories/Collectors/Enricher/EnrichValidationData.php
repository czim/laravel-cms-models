<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesFormStoreStrategies;
use Illuminate\Support\Arr;

class EnrichValidationData extends AbstractEnricherStep
{
    use ResolvesFormStoreStrategies;


    /**
     * Originally configured create rules.
     *
     * @var array
     */
    protected $originalCreateRules;

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

        $rules = $this->info->form->validation['create'] ?: [];

        // Store original rules so they may be used as a basis for update rules later.
        $this->originalCreateRules = $rules;

        $this->generatedRules = $this->getFormFieldBaseRules(true);

        if ( ! count($rules)) {
            $rules = $this->generatedRules;
        } else {
            $rules = $this->enrichRulesWithFormRules($rules, true);
        }

        $this->info->form->validation['create'] = $rules;

        return $this;
    }

    /**
     * @return $this
     */
    protected function enrichUpdateRules()
    {
        $rules = $this->info->form->validation['update'];

        $this->generatedRules = $this->getFormFieldBaseRules(false);

        // If no specific update rules were defined, use the
        // create rules as a starting point.

        if (null === $rules) {
            $rules = $this->originalCreateRules;
        }

        if ( ! count($rules)) {
            $rules = $this->generatedRules;
        } else {
            $rules = $this->enrichRulesWithFormRules($rules);
        }

        $this->info->form->validation['update'] = $rules;

        return $this;
    }

    /**
     * Enrich a given set of rules with form field data determined.
     *
     * @param array $rules      rules to be enriched
     * @param bool  $forCreate  whether the enrichment is for the 'create' section
     * @return array
     */
    protected function enrichRulesWithFormRules(array $rules, $forCreate = false)
    {
        // If rules are set, they should not overwritten,
        // and unkeyed string values should be enriched.
        // But keys not present should not be enriched or included.

        $enrichedRules    = [];
        $disabledRuleKeys = [];

        $replace = $this->info->form->validation->{($forCreate ? 'create' : 'update') . '_replace'};

        foreach ($rules as $key => $ruleParts) {

            // If the value for this key is false/null, the rule must be ignored entirely.
            if (false === $ruleParts || empty($ruleParts)) {
                $disabledRuleKeys[] = $key;
                continue;
            }

            // Enrich keys without values if possible
            if (is_string($ruleParts) && is_numeric($key)) {
                if (array_key_exists($ruleParts, $this->generatedRules)) {
                    $enrichedRules[ $ruleParts ] = $this->generatedRules[ $ruleParts ];
                }
                continue;
            }

            // Copy full definitions as is, normalized to array values
            if ( ! is_array($ruleParts)) {
                $ruleParts = explode('|', $ruleParts);
            }

            $enrichedRules[ $key ] = $ruleParts;
        }

        // If not replacing all rules, append any key not present
        // and not explicitly disabled.
        if ( ! $replace) {

            foreach ($this->generatedRulesMap as $fieldKey => $ruleKeys) {

                // The field key or any of the child rules keys may be disabled.
                if (in_array($fieldKey, $disabledRuleKeys) || array_key_exists($fieldKey, $enrichedRules)) {
                    continue;
                }

                foreach (array_diff($ruleKeys, $disabledRuleKeys) as $ruleKey) {

                    if (    ! array_key_exists($ruleKey, $this->generatedRules)
                        ||  ! count($this->generatedRules[ $ruleKey ])
                        ||  array_key_exists($ruleKey, $enrichedRules)
                    ) {
                        continue;
                    }

                    $enrichedRules[ $ruleKey ] = $this->generatedRules[ $ruleKey ];
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

            } catch (\Exception $e) {

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

        $fieldRules = $instance->validationRules($field, $this->info);

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
