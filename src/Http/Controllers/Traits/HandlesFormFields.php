<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Layout\ModelFormTabDataInterface;
use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Exceptions\StrategyApplicationException;
use Czim\CmsModels\Exceptions\StrategyRetrievalException;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesFormStoreStrategies;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use UnexpectedValueException;

trait HandlesFormFields
{
    use ResolvesFormStoreStrategies;

    /**
     * Returns a list of form field keys relevant for the current context,
     * depending on whether the model is being created or updated.
     *
     * @param bool $creating
     * @return string[]
     */
    protected function getRelevantFormFieldKeys($creating = false)
    {
        $fieldKeys = $this->getModelInformation()->form->getLayoutFormFieldKeys();

        // Filter out keys that should not be available
        $fieldKeys = array_filter($fieldKeys, function ($key) use ($creating) {

            if ( ! array_key_exists($key, $this->getModelInformation()->form->fields)) {
                throw new UnexpectedValueException(
                    "Layout field key '{$key}' not found in fields form data for "
                    . $this->getModelInformation()->modelClass()
                );
            }

            $fieldData = $this->getModelInformation()->form->fields[$key];

            if ( ! $this->allowedToUseFormFieldData($fieldData)) {
                return false;
            }

            if ($creating) {
                return $fieldData->create();
            }

            return $fieldData->update();
        });

        return $fieldKeys;
    }

    /**
     * Collects and returns current values for fields by key from a model.
     *
     * @param Model    $model
     * @param string[] $keys
     * @return array
     * @throws StrategyRetrievalException
     */
    protected function getFormFieldValuesFromModel(Model $model, array $keys)
    {
        $values = [];

        foreach ($keys as $key) {

            $field    = $this->getModelFormFieldDataForKey($key);
            $instance = $this->getFormFieldStoreStrategyInstanceForField($field);

            $instance->setFormFieldData($field);
            $instance->setParameters(
                $this->getFormFieldStoreStrategyParametersForField($field)
            );

            // If we're creating a new model, pre-select the form field for the active list parent
            if ( ! $model->exists && $this->isFieldValueBeDerivableFromListParent($field->key)) {

                try {
                    $values[ $key ] = $instance->valueForListParentKey($this->getListParentRecordKey());

                    // @codeCoverageIgnoreStart
                } catch (Exception $e) {
                    $message = "Failed retrieving value for form field '{$key}' (using " . get_class($instance)
                             . " (valueForListParentKey)): \n{$e->getMessage()}";

                    throw new StrategyRetrievalException($message, $e->getCode(), $e);
                    // @codeCoverageIgnoreEnd
                }

                continue;
            }

            try {
                $values[ $key ] = $instance->retrieve($model, $field->source ?: $field->key);

                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $message = "Failed retrieving value for form field '{$key}' (using " . get_class($instance)
                         . "): \n{$e->getMessage()}";

                throw new StrategyRetrievalException($message, $e->getCode(), $e);
                // @codeCoverageIgnoreEnd
            }
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
     * @throws StrategyApplicationException
     */
    protected function storeFormFieldValuesForModel(Model $model, array $values)
    {
        // Prepare field data and strategies

        /** @var ModelFormFieldDataInterface[]|ModelFormFieldData[] $fields */
        /** @var FormFieldStoreStrategyInterface[] $strategies */
        $fields     = [];
        $strategies = [];

        foreach (array_keys($values) as $key) {

            $field = $this->getModelFormFieldDataForKey($key);
            if ( ! $this->allowedToUseFormFieldData($field)) continue;

            $fields[ $key ]     = $field;
            $strategies[ $key ] = $this->getFormFieldStoreStrategyInstanceForField($fields[ $key ]);

            $strategies[ $key ]->setFormFieldData($fields[ $key ]);
            $strategies[ $key ]->setParameters(
                $this->getFormFieldStoreStrategyParametersForField($fields[ $key ])
            );
        }
        
        // First store values (such as necessary belongsTo-related instances),
        // before storing the model
        foreach ($values as $key => $value) {

            try {
                $strategies[ $key ]->store($model, $fields[ $key ]->source(), $value);

                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $class   = get_class($strategies[ $key ]);
                $message = "Failed storing value for form field '{$key}' (using $class): \n{$e->getMessage()}";

                throw new StrategyApplicationException($message, $e->getCode(), $e);
                // @codeCoverageIgnoreEnd
            }

        }

        // Save the model itself
        $success = $model->save();

        if ( ! $success) {
            return false;
        }

        // Then store values that can only be stored after the model exists
        // and is succesfully saved
        foreach ($values as $key => $value) {

            try {
                $strategies[ $key ]->storeAfter($model, $fields[ $key ]->source(), $value);

                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $class   = get_class($strategies[ $key ]);
                $message = "Failed storing value for form field '{$key}' (using $class (after)): \n{$e->getMessage()}";

                throw new StrategyApplicationException($message, $e->getCode(), $e);
                // @codeCoverageIgnoreEnd
            }
        }

        // If the model is still dirty after this, save it again
        if ($model->isDirty()) {
            $success = $model->save();
        }

        return $success;
    }

    /**
     * Returns associative array with form validation errors, keyed by field keys.
     *
     * This normalizes the errors to a nested structure that may be handled for display
     * by form field strategies.
     *
     * @return array
     */
    protected function getNormalizedFormFieldErrors()
    {
        $viewBags = session('errors');

        if ( ! ($viewBags instanceof ViewErrorBag) || ! count($viewBags)) {
            return [];
        }

        /** @var MessageBag $errorBag */
        $errorBag = head($viewBags->getBags());

        if ( ! $errorBag->any()) {
            return [];
        }

        $normalized = [];

        foreach ($errorBag->toArray() as $field => $errors) {
            array_set($normalized, $field, $errors);
        }

        return $normalized;
    }

    /**
     * Returns the error count keyed by tab pane keys.
     *
     * @return array
     */
    protected function getErrorCountsPerTabPane()
    {
        $normalizedErrorKeys = array_keys($this->getNormalizedFormFieldErrors());

        if ( ! count($normalizedErrorKeys)) {
            return [];
        }

        $errorCount = [];

        foreach ($this->getModelInformation()->form->layout() as $key => $node) {

            if ( ! ($node instanceof ModelFormTabDataInterface)) {
                continue;
            }

            $fieldKeys = $this->getFieldKeysForTab($key);

            $errorCount[ $key ] = count(array_intersect($normalizedErrorKeys, $fieldKeys));
        }

        return $errorCount;
    }

    /**
     * Returns the form field keys that are descendants of a given tab pane.
     *
     * @param string $tab   key for a tab pane
     * @return string[]
     */
    protected function getFieldKeysForTab($tab)
    {
        $layout = $this->getModelInformation()->form->layout();

        if ( ! array_key_exists($tab, $layout)) {
            return [];
        }

        $tabData = $layout[ $tab ];

        if ( ! ($tabData instanceof ModelFormTabDataInterface)) {
            return [];
        }

        return $tabData->descendantFieldKeys();
    }

    /**
     * Returns whether current user has permission to use the form field.
     *
     * @param ModelFormFieldDataInterface $field
     * @return bool
     */
    protected function allowedToUseFormFieldData(ModelFormFieldDataInterface $field)
    {
        if ( ! $field->adminOnly() && ! count($field->permissions())) {
            return true;
        }

        $user = $this->getCore()->auth()->user();

        if ( ! $user || $field->adminOnly() && ! $user->isAdmin()) {
            return false;
        }

        if ($user->isAdmin() || ! count($field->permissions())) {
            return true;
        }

        return $user->can($field->permissions());
    }


    /**
     * @return CoreInterface
     */
    abstract protected function getCore();

    /**
     * @param string $field
     * @return bool
     */
    abstract protected function isFieldValueBeDerivableFromListParent($field);

    /**
     * @return mixed|null
     */
    abstract protected function getListParentRecordKey();

}
