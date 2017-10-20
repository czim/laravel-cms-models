<?php
namespace Czim\CmsModels\Exceptions;

use Czim\CmsModels\Http\Controllers\DefaultModelController;
use Illuminate\Validation\ValidationException;

class ModelValidationException extends ValidationException
{

    /**
     * Get all of the validation error messages.
     *
     * @return array
     */
    public function errors()
    {
        $errors = parent::errors();

        if ( ! empty($errors)) {
            $errors[ $this->generalErrorsKey() ] = $this->collectGeneralErrors($errors);
        }

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

}
