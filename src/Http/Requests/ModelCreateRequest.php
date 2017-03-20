<?php
namespace Czim\CmsModels\Http\Requests;

class ModelCreateRequest extends AbstractModelFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        $rules = $this->modelInformation->form->validation->create();

        if ($class = $this->modelInformation->form->validation->rulesClass()) {

            $instance = $this->makeValidationRulesClassInstance($class);

            $rules = $instance->create($rules);
        }

        return $rules;
    }

}
