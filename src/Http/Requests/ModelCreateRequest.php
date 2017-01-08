<?php
namespace Czim\CmsModels\Http\Requests;

class ModelCreateRequest extends AbstractModelFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return $this->modelInformation->form->validation->create();
    }

}
