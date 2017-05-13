<?php
namespace Czim\CmsModels\Support\Form;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Layout\ModelFormTabDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\FormStoreStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Form\FormDataStorerInterface;
use Czim\CmsModels\Exceptions\StrategyApplicationException;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesFormStoreStrategies;
use Exception;
use Illuminate\Support\MessageBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ViewErrorBag;

class FormDataStorer implements FormDataStorerInterface
{
    use ResolvesFormStoreStrategies;

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var FormStoreStrategyFactoryInterface
     */
    protected $storeFactory;

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $information;


    /**
     * @param CoreInterface                     $core
     * @param FormStoreStrategyFactoryInterface $storeFactory
     */
    public function __construct(CoreInterface $core, FormStoreStrategyFactoryInterface $storeFactory)
    {
        $this->core         = $core;
        $this->storeFactory = $storeFactory;
    }

    /**
     * @param ModelInformationInterface $information
     * @return $this
     */
    public function setModelInformation(ModelInformationInterface $information)
    {
        $this->information = $information;

        return $this;
    }

    /**
     * Stores submitted form field data on a model.
     *
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function store(Model $model, array $data)
    {
        // todo sanitize as needed

        return $this->storeFormFieldValuesForModel($model, $data);
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

        $user = $this->core->auth()->user();

        if ( ! $user || $field->adminOnly() && ! $user->isAdmin()) {
            return false;
        }

        if ($user->isAdmin() || ! count($field->permissions())) {
            return true;
        }

        return $user->can($field->permissions());
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     */
    protected function getModelInformation()
    {
        return $this->information;
    }

}