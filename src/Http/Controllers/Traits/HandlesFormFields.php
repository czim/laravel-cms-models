<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\Data\ModelFormLayoutNodeInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

trait HandlesFormFields
{

    /**
     * Returns a list of form field keys relevant for the current context,
     * depending on whether the model is being created or updated.
     *
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
            }

            return $this->getModelInformation()->form->fields[$key]->update();
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

            $field    = $this->getModelFormFieldDataForKey($key);
            $instance = $this->getFormFieldStoreStrategyInstanceForField($field);

            $values[ $key ] = $instance->retrieve($model, $field->source ?: $field->key);
        }

        return $values;
    }

    /**
     * Stores filled in form field data for a model instance.
     * Note that this will persist the model if it is a new instance.
     *
     * @param Model $model
     * @param array $values     associative array with form data, should only include actual field data
     * @return bool
     */
    protected function storeFormFieldValuesForModel(Model $model, array $values)
    {
        // Prepare field data and strategies

        /** @var ModelFormFieldDataInterface[]|ModelFormFieldData[] $fields */
        /** @var FormFieldStoreStrategyInterface[] $strategies */
        $fields     = [];
        $strategies = [];

        foreach (array_keys($values) as $key) {
            $fields[ $key ]     = $this->getModelFormFieldDataForKey($key);
            $strategies[ $key ] = $this->getFormFieldStoreStrategyInstanceForField($fields[ $key ]);
        }

        // First store values (such as necessary belongsTo-related instances),
        // before storing the model
        foreach ($values as $key => $value) {
            $strategies[ $key ]->store($model, $fields[ $key ]->source(), $value);
        }

        // Save the model itself
        $success = $model->save();

        if ( ! $success) {
            return false;
        }

        // Then store values that can only be stored after the model exists
        // and is succesfully saved
        foreach ($values as $key => $value) {
            $strategies[ $key ]->storeAfter($model, $fields[ $key ]->source(), $value);
        }

        // If the model is still dirty after this, save it again
        if ($model->isDirty()) {
            $success = $model->save();
        }

        return $success;
    }

    /**
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return FormFieldStoreStrategyInterface
     */
    protected function getFormFieldStoreStrategyInstanceForField(ModelFormFieldDataInterface $field)
    {
        $strategy = $this->resolveFormFieldStoreStrategyClass($field->store_strategy);

        return new $strategy;
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
