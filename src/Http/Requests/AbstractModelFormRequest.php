<?php
namespace Czim\CmsModels\Http\Requests;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Http\Requests\ValidationRulesInterface;
use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleDecoratorInterface;
use Czim\CmsModels\Exceptions\ModelValidationException;
use Czim\CmsModels\Http\Controllers\DefaultModelController;
use Czim\CmsModels\Http\Controllers\Traits\HandlesFormFields;
use Illuminate\Contracts\Validation\Validator;
use InvalidArgumentException;

class AbstractModelFormRequest extends AbstractModelRequest
{
    use HandlesFormFields;

    /**
     * Returns post-processed validation rules.
     *
     * @return array
     */
    public function processedRules()
    {
        $decorator = $this->getRuleDecorator();

        return $decorator->decorate(
            $this->container->call([$this, 'rules'])
        );
    }

    /**
     * {@inheritdoc}
     * @throws \Czim\CmsModels\Exceptions\ModelValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw (new ModelValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
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
     * @return ValidationRuleDecoratorInterface
     */
    protected function getRuleDecorator()
    {
        return app(ValidationRuleDecoratorInterface::class);
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
