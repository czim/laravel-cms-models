<?php
namespace Czim\CmsModels\Contracts\Http\Requests;

use Illuminate\Database\Eloquent\Model;

interface ValidationRulesInterface
{

    /**
     * Returns (decorated) validation rules to use when creating a model.
     *
     * @param array $rules
     * @return array
     */
    public function create(array $rules);

    /**
     * Returns (decorated) validation rules to use when updating a model.
     *
     * @param array $rules
     * @param Model $model      the model being updated, if available
     * @return array
     */
    public function update(array $rules, Model $model = null);

}
