<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\AttributeFormStrategy;

class AttributeStrategyResolver
{

    /**
     * Determines a general field strategy string for given attribute data.
     *
     * @param ModelAttributeData $data
     * @return string|null
     */
    public function determineStrategy(ModelAttributeData $data)
    {
        $type = null;

        switch ($data->cast) {

            case 'boolean':
            case 'bool':
                $type = AttributeFormStrategy::BOOLEAN_CHECKBOX;
                break;

            case 'integer':
            case 'int':
                $type = AttributeFormStrategy::NUMERIC_INTEGER;
                break;

            case 'float':
            case 'double':
                $type = AttributeFormStrategy::NUMERIC_DECIMAL;
                break;

            case 'string':
                switch ($data->type) {

                    case 'enum':
                        $type = AttributeFormStrategy::SELECT_DROPDOWN;
                        break;

                    case 'year':
                        $type = AttributeFormStrategy::NUMERIC_YEAR;
                        break;

                    case 'varchar':
                    case 'char':
                    case 'tinytext':
                        $type = AttributeFormStrategy::TEXT;
                        break;

                    case 'text':
                    case 'mediumtext':
                    case 'longtext':
                    case 'blob';
                    case 'mediumblob';
                    case 'longblob';
                    case 'binary';
                    case 'varbinary';
                        $type = AttributeFormStrategy::TEXTAREA;
                        break;
                }
                break;

            case 'date':
                switch ($data->type) {

                    case 'date':
                        $type = AttributeFormStrategy::DATEPICKER_DATE;
                        break;

                    case 'time':
                        $type = AttributeFormStrategy::DATEPICKER_TIME;
                        break;

                    case 'datetime':
                    case 'timestamp':
                        $type = AttributeFormStrategy::DATEPICKER_DATETIME;
                        break;
                }
                break;

            case 'array':
            case 'json':
                $type = null;
                break;
        }

        return $type;
    }

}
