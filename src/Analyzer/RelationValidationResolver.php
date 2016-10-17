<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Data\ModelFormFieldData;

class RelationValidationResolver
{

    /**
     * Determines validation rules for given relation data.
     *
     * @param ModelRelationData  $relation
     * @param ModelFormFieldData $field
     * @return array|false
     */
    public function determineValidationRules(ModelRelationData $relation, ModelFormFieldData $field)
    {
        $rules = [];

        if ($field->required() && ! $field->translated()) {
            $rules[] = 'required';
        }

        // todo
        // make validation rules as far as possible, based on the key type (incrementing?)
        // or using exists:: ...

        return $rules;
    }

}
