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
     * Performs enrichment of validation rules based on form field strategies.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->form->fields)) {
            return;
        }

        $rules = $this->info->validation['create'] ?: [];

        foreach ($this->info->form->fields as $field) {

            $instance = $this->getFormFieldStoreStrategyInstanceForField($field);

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

        $this->info->validation['create'] = $rules;
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     */
    protected function getModelInformation()
    {
        return $this->info;
    }

}
