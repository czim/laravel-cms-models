<?php
namespace Czim\CmsModels\Http\Requests;

class ModelUpdateRequest extends AbstractModelFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return $this->modelInformation->form->validation->update();
    }

}
