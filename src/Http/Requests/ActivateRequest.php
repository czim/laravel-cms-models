<?php
namespace Czim\CmsModels\Http\Requests;

class ActivateRequest extends Request
{

    public function rules()
    {
        return [
            'activate' => 'required|boolean',
        ];
    }

    public function authorize()
    {
        return true;
    }

}
