<?php
namespace Czim\CmsModels\Contracts\Http\Requests;

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
     * @return array
     */
    public function update(array $rules);

}
