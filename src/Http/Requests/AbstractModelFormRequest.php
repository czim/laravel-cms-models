<?php
namespace Czim\CmsModels\Http\Requests;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Http\Requests\ValidationRulesInterface;
use Czim\CmsModels\Http\Controllers\DefaultModelController;
use Czim\CmsModels\Http\Controllers\Traits\HandlesFormFields;
use Czim\CmsModels\Support\Translation\DecoratesTranslatedValidationRules;
use Illuminate\Contracts\Validation\Validator;
use InvalidArgumentException;

class AbstractModelFormRequest extends AbstractModelRequest
{
    use HandlesFormFields,
        DecoratesTranslatedValidationRules;

    /**
     * Returns post-processed validation rules.
     *
     * @return array
     */
    public function processedRules()
    {
        return $this->decorateTranslatedValidationRules(
            $this->container->call([$this, 'rules'])
        );
    }

    /**
     * Format the errors from the given Validator instance.
     *
     * Adjusted to reformat errors and add __general__ errors.
     *
     * {@inheritdoc}
     */
    protected function formatErrors(Validator $validator)
    {
        $errors = $validator->getMessageBag()->toArray();

        $errors[ $this->generalErrorsKey() ] = $this->collectGeneralErrors($errors);

        return $errors;
    }

    /**
     * Returns array with general error messages.
     *
     * @param array $errors
     * @return string[]
     */
    protected function collectGeneralErrors(array $errors)
    {
        $general = [];

        // If there is any error at all, add a general 'there are errors' message.
        if (count($errors)) {
            $general[] = cms_trans('common.errors.form.general-validation');
        }

        return $general;
    }

    /**
     * Returns the key under which to group general form errors.
     *
     * @return string
     */
    protected function generalErrorsKey()
    {
        return DefaultModelController::GENERAL_ERRORS_KEY;
    }

    /**
     * @return CoreInterface
     */
    protected function getCore()
    {
        return app(CoreInterface::class);
    }

    /**
     * Not relevant here, only for retrieving the actual values.
     *
     * @param string $field
     * @return bool
     */
    protected function isFieldValueBeDerivableFromListParent($field)
    {
        return false;
    }

    /**
     * Not relevant here, only for retrieving the actual values.
     *
     * @return mixed|null
     */
    protected function getListParentRecordKey()
    {
        return null;
    }

    /**
     * Checks a class and makes an instance of a validation rules decorator.
     *
     * @param string $class
     * @return ValidationRulesInterface
     */
    protected function makeValidationRulesClassInstance($class)
    {
        if ( ! class_exists($class)) {
            throw new InvalidArgumentException("{$class} does not exist for validation rules instantiation");
        }

        if ( ! is_a($class, ValidationRulesInterface::class, true)) {
            throw new InvalidArgumentException("{$class} does not implement ValidationRulesInterface");
        }

        return app($class);
    }

}
