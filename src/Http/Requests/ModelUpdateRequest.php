<?php
namespace Czim\CmsModels\Http\Requests;

use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Http\Controllers\Traits\AppliesRepositoryContext;

class ModelUpdateRequest extends AbstractModelFormRequest
{
    use AppliesRepositoryContext;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = $this->modelInformation->form->validation->update();

        if ($class = $this->modelInformation->form->validation->rulesClass()) {

            $instance = $this->makeValidationRulesClassInstance($class);

            $rules = $instance->update($rules, $this->getTargetedModel());
        }

        return $rules;
    }

    /**
     * Returns the model instance being updated.
     *
     * @return mixed
     */
    protected function getTargetedModel()
    {
        $key = $this->getTargetedModelKey();

        return $this->makeModelRepository()->findOrFail($key);
    }

    /**
     * Returns the key for the model being updated.
     *
     * @return mixed
     */
    protected function getTargetedModelKey()
    {
        return last($this->segments());
    }

    /**
     * Sets up the model repository for the relevant model.
     *
     * @return ModelRepositoryInterface
     */
    protected function makeModelRepository()
    {
        $repository = app(ModelRepositoryInterface::class, [
            $this->modelInformation->modelClass()
        ]);

        $this->applyRepositoryContext($repository, $this->modelInformation);

        return $repository;
    }

}
