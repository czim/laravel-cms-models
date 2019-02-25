<?php
namespace Czim\CmsModels\ModelConfig;

class ModelForm
{

    /**
     * @var array
     */
    protected $main = [];

    /**
     * @var FormField[]
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $validationMeta = [];

    /**
     * @var Validation[]
     */
    protected $validation = [];

    /**
     * @var array
     */
    protected $layout = [];


    public function toArray(): array
    {
        return $this->buildFormArray();
    }


    /**
     * Sets a list of field definitions
     *
     * In addition to any Field-defined fields.
     *
     * @param FormField[] $fields
     * @return ModelForm|$this
     */
    public function fields(array $fields): ModelForm
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Sets the field layout for the form
     *
     * @param array $layout     field keys and FormTab instances may be combined
     * @return ModelForm|$this
     */
    public function layout(array $layout): ModelForm
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Sets a list of validation definitions
     *
     * In addition to any FormField-defined validation rules
     *
     * @param Validation[] $validation
     * @return ModelForm|$this
     */
    public function validation(array $validation): ModelForm
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Set a view to include before the form
     *
     * @param string $view
     * @param array  $variables     optional list of variable names to pass through to the included view
     * @return ModelForm|$this
     */
    public function viewBefore(string $view, array $variables = []): ModelForm
    {
        $this->main['before'] = compact('view', 'variables');

        return $this;
    }

    /**
     * Set a view to include after the form
     *
     * @param string $view
     * @param array  $variables     optional list of variable names to pass through to the included view
     * @return ModelForm|$this
     */
    public function viewAfter(string $view, array $variables = []): ModelForm
    {
        $this->main['after'] = compact('view', 'variables');

        return $this;
    }

    /**
     * Set a view to include in the <form> tag, but before the form content
     *
     * @param string $view
     * @param array  $variables     optional list of variable names to pass through to the included view
     * @return ModelForm|$this
     */
    public function viewBeforeContent(string $view, array $variables = []): ModelForm
    {
        $this->main['before_form'] = compact('view', 'variables');

        return $this;
    }

    /**
     * Set a view to include in the <form> tag, but after the form content
     *
     * @param string $view
     * @param array  $variables     optional list of variable names to pass through to the included view
     * @return ModelForm|$this
     */
    public function viewAfterContent(string $view, array $variables = []): ModelForm
    {
        $this->main['after_form'] = compact('view', 'variables');

        return $this;
    }

    /**
     * Class to use for decorating (or providing) validation rules.
     *
     * @param string $class FQN Instance of ValidationRulesInterface.
     * @return ModelForm|$this
     */
    public function validationDecorator(string $class): ModelForm
    {
        $this->validationMeta['rules_class'] = $class;

        return $this;
    }

    /**
     * Configured validation rules for create form overwrite all auto-generated rules.
     *
     * @return ModelForm|$this
     */
    public function replaceGeneratedValidationForCreate(): ModelForm
    {
        $this->validationMeta['create_replace'] = true;

        return $this;
    }

    /**
     * Configured validation rules for update form overwrite all auto-generated rules.
     *
     * @return ModelForm|$this
     */
    public function replaceGeneratedValidationForUpdate(): ModelForm
    {
        $this->validationMeta['update_replace'] = true;

        return $this;
    }


    protected function buildFormArray(): array
    {
        $array = $this->main;

        if (count($this->layout)) {
            $array['layout'] = $this->layout;
        }

        $this->applyFieldsDataToArray($array);
        $this->applyValidationDataToArray($array);

        return $array;
    }

    protected function applyFieldsDataToArray(array &$array): void
    {
        if ( ! count($this->fields)) {
            return;
        }

        foreach ($this->fields as $field) {

            $array['fields'][ $field->getKey() ] = $field->toArray();
        }
    }

    protected function applyValidationDataToArray(array &$array): void
    {
        if ( ! count($this->validationMeta) && ! count($this->validation)) {
            return;
        }

        if (count($this->validationMeta)) {
            $array['validation'] = $this->validationMeta;
        }

        foreach ($this->fields as $field) {

            $array['fields'][ $field->getKey() ] = $field->toArray();
        }
    }

}
