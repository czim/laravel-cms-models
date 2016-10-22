<?php
namespace Czim\CmsModels\Http\Requests;

class ModelUpdateRequest extends AbstractModelFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        $updateRules = array_get($this->modelInformation->validation, 'update');

        if (null !== $updateRules) {
            return $updateRules;
        }

        // Fall back to create rules if required
        return array_get($this->modelInformation->validation, 'create', []);
    }

}
