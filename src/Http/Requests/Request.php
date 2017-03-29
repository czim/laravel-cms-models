<?php
namespace Czim\CmsModels\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

abstract class Request extends FormRequest
{

    /**
     * Overridden to only return JSON response if explicitly requested.
     *
     * {@inheritdoc}
     */
    public function response(array $errors)
    {
        if ($this->wantsJson()) {
            return new JsonResponse($errors, 422);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }

}
