<?php
namespace Czim\CmsModels\Http\Requests;

class ModelMetaReferenceRequest extends Request
{

    public function rules()
    {
        return [
            'model'     => 'required|string',
            'type'      => 'required|string',
            'key'       => 'required|string',
            'search'    => 'string',
        ];
    }

    public function authorize()
    {
        return true;
    }

}
