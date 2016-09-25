<?php
namespace Czim\CmsModels\Http\Requests;

class OrderUpdateRequest extends Request
{

    public function rules()
    {
        return [
            'position' => 'required',
        ];
    }

    public function authorize()
    {
        return true;
    }

}
