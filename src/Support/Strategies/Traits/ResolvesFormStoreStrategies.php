<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Exceptions\StrategyResolutionException;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Czim\CmsModels\Support\Data\ModelInformation;

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

        $strategy = $this->resolveFormFieldStoreStrategyClass($strategy);

        return new $strategy;
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
        return $this->getModelInformation()->form->fields[ $fieldKey ];
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a form field store
     * interface implementation, or a configured alias.
     *
     * @param string $strategy
     * @return string           returns full class namespace if it was resolved succesfully
     * @throws StrategyResolutionException
     */
    protected function resolveFormFieldStoreStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.form.store-aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, FormFieldStoreStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixFormFieldStoreStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, FormFieldStoreStrategyInterface::class, true)) {
            return $strategy;
        }

        if ($strategy) {
            throw new StrategyResolutionException("Could not find form store class for strategy '{$strategy}'");
        }

        return config('cms-models.strategies.form.default-store-strategy');
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixFormFieldStoreStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.form.default-store-namespace'), '\\') . '\\' . $class;
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     */
    abstract protected function getModelInformation();

}
