<?php
namespace Czim\CmsModels\Http\Requests;

class ModelUpdateRequest extends AbstractModelFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        $rules = $this->modelInformation->form->validation->update();

        if ($class = $this->modelInformation->form->validation->rulesClass()) {

            $instance = $this->makeValidationRulesClassInstance($class);

            $rules = $instance->update($rules);
        }

        return $rules;
    }

}
