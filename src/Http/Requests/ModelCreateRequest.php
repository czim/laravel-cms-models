<?php
namespace Czim\CmsModels\Http\Requests;

class ModelCreateRequest extends AbstractModelRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return array_get($this->modelInformation->validation, 'create', []);
    }

}
