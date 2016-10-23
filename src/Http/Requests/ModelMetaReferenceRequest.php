<?php
namespace Czim\CmsModels\Http\Requests;

class ModelMetaReferenceRequest extends Request
{

    public function rules()
    {
        return [
            'model'     => 'required|string',
            'search'    => 'string',
            'target'    => 'string',
            'reference' => 'string',
            'strategy'  => 'string',
        ];
    }

    public function authorize()
    {
        return true;
    }

}
