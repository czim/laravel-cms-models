<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelFormLayoutNodeInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

trait HandlesFormFields
{

    /**
     * @param bool $creating
     * @return string[]
     */
    protected function getRelevantFormFieldKeys($creating = false)
    {
        $layout = $this->getModelInformation()->form->layout();

        $fieldKeys = [];

        foreach ($layout as $key => $value) {

            if ($value instanceof ModelFormLayoutNodeInterface) {
                $fieldKeys = array_merge($fieldKeys, $this->getNestedFormFieldKeys($value));
                continue;
            }

            if ( ! is_string($value)) {
                continue;
            }

            $fieldKeys[] = $value;
        }

        $fieldKeys = array_unique($fieldKeys);

        // Filter out keys that should not be available
        $fieldKeys = array_filter($fieldKeys, function ($key) use ($creating) {

            if ( ! array_key_exists($key, $this->getModelInformation()->form->fields)) {
                throw new UnexpectedValueException(
                    "Layout field key '{$key}' not found in fields form data for "
                    . $this->getModelInformation()->modelClass()
                );
            }

            if ($creating) {
                return $this->getModelInformation()->form->fields[$key]->create();
            } else {
                return $this->getModelInformation()->form->fields[$key]->update();
            }
        });

        return $fieldKeys;
    }

    /**
     * @param ModelFormLayoutNodeInterface $node
     * @return string[]
     */
    protected function getNestedFormFieldKeys(ModelFormLayoutNodeInterface $node)
    {
        $children = $node->children();

        $fieldKeys = [];

        foreach ($children as $key => $value) {

            if ($value instanceof ModelFormLayoutNodeInterface) {
                $fieldKeys = array_merge($fieldKeys, $this->getNestedFormFieldKeys($value));
                continue;
            }

            if ( ! is_string($value)) {
                continue;
            }

            $fieldKeys[] = $value;
        }

        return $fieldKeys;
    }

    /**
     * Collects and returns current values for fields by key from a model.
     *
     * @param Model    $model
     * @param string[] $keys
     * @return array
     */
    protected function getFormFieldValuesFromModel(Model $model, array $keys)
    {
        $values = [];

        foreach ($keys as $key) {

            $field = $this->getModelInformation()->form->fields[ $key ];

            $strategy = $field->store_strategy;

            $strategy = $this->resolveFormFieldStoreStrategyClass($strategy);

            /** @var FormFieldStoreStrategyInterface $instance */
            $instance = new $strategy;

            $values[ $key ] = $instance->retrieve($model, $field->source ?: $field->key);
        }

        return $values;
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a form field store
     * interface implementation, or a configured alias.
     *
     * @param string $strategy
     * @return string           returns full class namespace if it was resolved succesfully
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
