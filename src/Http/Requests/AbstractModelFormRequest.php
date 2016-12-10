<?php
namespace Czim\CmsModels\Http\Requests;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Http\Controllers\DefaultModelController;
use Czim\CmsModels\Http\Controllers\Traits\HandlesFormFields;
use Czim\CmsModels\Support\Translation\DecoratesTranslatedValidationRules;
use Illuminate\Contracts\Validation\Validator;

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

}
