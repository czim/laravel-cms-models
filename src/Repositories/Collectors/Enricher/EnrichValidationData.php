<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
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
     * Performs enrichment of validation rules based on form field strategies.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->form->fields)) {
            return;
        }

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
        $rules = $this->info->validation['create'] ?: [];

        // Store original rules so they may be used as a basis for update rules later.
        $this->originalCreateRules = $rules;

        $formRules = $this->getFormFieldBaseRules(true);

        if ( ! count($rules)) {
            $rules = $formRules;
        } else {
            $rules = $this->enrichRulesWithFormRules($rules, $formRules);
        }

        $this->info->validation['create'] = $rules;

        return $this;
    }

    /**
     * @return $this
     */
    protected function enrichUpdateRules()
    {
        $rules = $this->info->validation['update'];

        $formRules = $this->getFormFieldBaseRules(false);

        // If no specific update rules were defined, use the
        // create rules as a starting point.

        if (null === $rules) {
            $rules = $this->originalCreateRules;
        }

        if ( ! count($rules)) {
            $rules = $formRules;
        } else {
            $rules = $this->enrichRulesWithFormRules($rules, $formRules);
        }

        $this->info->validation['update'] = $rules;

        return $this;
    }

    /**
     * Enrich a given set of rules with form field data determined.
     *
     * @param array $rules      rules to be enriched
     * @param array $formRules  rules determined based on form field data
     * @return array
     */
    protected function enrichRulesWithFormRules(array $rules, array $formRules)
    {
        // If rules are set, they should not overwritten,
        // and unkeyed string values should be enriched.
        // But keys not present should not be enriched or included.

        $enrichedRules = [];

        foreach ($rules as $key => $ruleParts) {

            // Enrich keys without values if possible
            if (is_string($ruleParts) && is_numeric($key)) {
                if (array_key_exists($ruleParts, $formRules)) {
                    $enrichedRules[ $ruleParts ] = $formRules[ $ruleParts ];
                }
                continue;
            }

            // Copy full definitions as is, normalized to array values
            if ( ! is_array($ruleParts)) {
                $ruleParts = explode('|', $ruleParts);
            }

            $enrichedRules[ $key ] = $ruleParts;
        }

        return $enrichedRules;
    }

    /**
     * Returns rules determined by form field strategies.
     *
     * @param bool $forCreate
     * @return array
     */
    protected function getFormFieldBaseRules($forCreate = true)
    {
        $rules = [];

        foreach ($this->info->form->fields as $field) {

            // Leave out fields that are not relevant
            if ($forCreate && ! $field->create() || ! $forCreate && ! $field->update()) {
                continue;
            }

            $instance = $this->getFormFieldStoreStrategyInstanceForField($field);

            $instance->setFormFieldData($field);
            $instance->setParameters(
                $this->getFormFieldStoreStrategyParametersForField($field)
            );

            $fieldRules = $instance->validationRules($field, $this->info);

            if (false === $fieldRules) {
                continue;
            }

            if (Arr::isAssoc($fieldRules)) {
                foreach ($fieldRules as $key => $nestedFieldRules) {
                    $rules[ $key ] = $nestedFieldRules;
                }
            } else {
                $rules[ $field->key() ] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     */
    protected function getModelInformation()
    {
        return $this->info;
    }

}
