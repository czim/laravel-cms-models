<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Strategies\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Contracts\Support\Factories\FormStoreStrategyFactoryInterface;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use UnexpectedValueException;

trait ResolvesFormStoreStrategies
{

    /**
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return FormFieldStoreStrategyInterface
     */
    protected function getFormFieldStoreStrategyInstanceForField(ModelFormFieldDataInterface $field)
    {
        $strategy = $field->store_strategy ?: '';

        if (false !== strpos($strategy, ':')) {
            $strategy = head(explode(':', $strategy));
        }

        /** @var FormStoreStrategyFactoryInterface $strategy */
        $factory = app(FormStoreStrategyFactoryInterface::class);

        return $factory->make($strategy);
    }

    /**
     * Returns parameters that should be passed into the store strategy instance.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return array
     */
    protected function getFormFieldStoreStrategyParametersForField(ModelFormFieldDataInterface $field)
    {
        $strategy = $field->store_strategy ?: '';

        $pos = strpos($strategy, ':');

        if (false === $pos) {
            return [];
        }

        return array_map('trim', explode(',', substr($strategy, $pos + 1)));
    }

    /**
     * @param $fieldKey
     * @return ModelFormFieldDataInterface|ModelFormFieldData
     */
    protected function getModelFormFieldDataForKey($fieldKey)
    {
        if ( ! array_key_exists($fieldKey, $this->getModelInformation()->form->fields)) {
            throw new UnexpectedValueException("Form field with key '{$fieldKey}' not set in model information");
        }

        return $this->getModelInformation()->form->fields[ $fieldKey ];
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     */
    abstract protected function getModelInformation();

}
