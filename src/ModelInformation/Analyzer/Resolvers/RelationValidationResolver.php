<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Resolvers;

use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;

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
        } else {
            // Anything that is not required should by default be explicitly nullable
            // since Laravel 5.4.
            $rules[] = 'nullable';
        }

        // todo
        // make validation rules as far as possible, based on the key type (incrementing?)
        // or using exists:: ...

        return $rules;
    }

}
