<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Resolvers;

use Czim\CmsModels\Http\Controllers\FormFieldStrategies\AbstractFormFieldStoreStrategy;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Czim\CmsModels\Support\Enums\AttributeCast;

class AttributeValidationResolver
{

    /**
     * Determines validation rules for given attribute data.
     *
     * @param ModelAttributeData $attribute
     * @param ModelFormFieldData $field
     * @return array|false
     */
    public function determineValidationRules(ModelAttributeData $attribute, ModelFormFieldData $field)
    {
        $rules = [];

        $required = $field->required();

        switch ($attribute->cast) {

            case AttributeCast::BOOLEAN:
                $required = false;
                break;

            case AttributeCast::INTEGER:
                $rules[] = 'integer';
                break;

            case AttributeCast::FLOAT:
                $rules[] = 'numeric';
                break;

            case AttributeCast::STRING:
                switch ($attribute->type) {

                    case 'enum':
                        $rules[] = 'in:' . implode(',', $attribute->values);
                        break;

                    case 'year':
                        $rules[] = 'digits:4';
                        break;

                    case 'varchar':
                        $rules[] = 'string';
                        if ($attribute->length) {
                            $rules[] = 'max:' . $attribute->length;
                        }
                        break;

                    case 'tinytext':
                        $rules[] = 'string';
                        $rules[] = 'max:255';
                        break;

                    case 'char':
                        $rules[] = 'string';
                        $rules[] = 'max:' . $attribute->length;
                        break;

                    case 'text':
                    case 'mediumtext':
                    case 'longtext':
                    case 'blob';
                    case 'mediumblob';
                    case 'longblob';
                    case 'binary';
                    case 'varbinary';
                        $rules[] = 'string';
                        break;
                }
                break;

            case AttributeCast::DATE:
                switch ($attribute->type) {

                    case 'date':
                    case 'datetime':
                    case 'timestamp':
                        $rules[] = 'date';
                        break;

                    case 'time':
                        $rules[] = 'regex:#^\d{1,2}:\d{1,2}(:\d{1,2})?$#';
                        break;
                }
                break;

            case AttributeCast::JSON:
                $rules[] = 'json';
                break;
        }

        if ($required) {
            // Special case for translated fields: required should be treated as required only if
            // any other translated fields for that locale are entered.
            // This should be handled by the class that requested the attribute validation rules.
            /** @see AbstractFormFieldStoreStrategy */
            $rules[] = 'required';
        }

        return $rules;
    }

}
