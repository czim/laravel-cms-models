<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\FormStoreStrategy;
use Czim\CmsModels\Support\Enums\ListDisplayStrategy;

class AttributeStrategyResolver
{

    /**
     * Determines a list column display strategy string for given attribute data.
     *
     * @param ModelAttributeData $data
     * @return string|null
     */
    public function determineListDisplayStrategy(ModelAttributeData $data)
    {
        $type = null;

        switch ($data->cast) {

            case 'boolean':
            case 'bool':
                if ($data->nullable) {
                    $type = ListDisplayStrategy::CHECK_NULLABLE;
                } else {
                    $type = ListDisplayStrategy::CHECK;
                }
                break;

            case 'date':
                switch ($data->type) {

                    case 'date':
                        $type = ListDisplayStrategy::DATE;
                        break;

                    case 'time':
                        $type = ListDisplayStrategy::TIME;
                        break;

                    case 'datetime':
                    case 'timestamp':
                        $type = ListDisplayStrategy::DATETIME;
                        break;
                }
                break;

            // Special case: stapler file attachment
            case 'stapler-attachment':
                if ($data->type === 'image') {
                    $type = ListDisplayStrategy::STAPLER_THUMBNAIL;
                } else {
                    $type = ListDisplayStrategy::STAPLER_FILENAME;
                }
                break;
        }

        return $type;
    }

    /**
     * Determines a form field display strategy string for given attribute data.
     *
     * @param ModelAttributeData $data
     * @return string|null
     */
    public function determineFormDisplayStrategy(ModelAttributeData $data)
    {
        $type = null;

        switch ($data->cast) {

            case 'boolean':
            case 'bool':
                if ($data->nullable) {
                    $type = FormDisplayStrategy::BOOLEAN_DROPDOWN;
                } else {
                    $type = FormDisplayStrategy::BOOLEAN_CHECKBOX;
                }
                break;

            case 'integer':
            case 'int':
                $type = FormDisplayStrategy::NUMERIC_INTEGER;
                break;

            case 'float':
            case 'double':
                $type = FormDisplayStrategy::NUMERIC_DECIMAL;
                break;

            case 'string':
                switch ($data->type) {

                    case 'enum':
                        $type = FormDisplayStrategy::SELECT_DROPDOWN;
                        break;

                    case 'year':
                        $type = FormDisplayStrategy::NUMERIC_YEAR;
                        break;

                    case 'varchar':
                    case 'char':
                    case 'tinytext':
                        $type = FormDisplayStrategy::TEXT;
                        break;

                    case 'text':
                    case 'mediumtext':
                    case 'longtext':
                        $type = FormDisplayStrategy::WYSIWYG;
                        break;

                    case 'blob';
                    case 'mediumblob';
                    case 'longblob';
                    case 'binary';
                    case 'varbinary';
                        $type = FormDisplayStrategy::TEXTAREA;
                        break;
                }
                break;

            case 'date':
                switch ($data->type) {

                    case 'date':
                        $type = FormDisplayStrategy::DATEPICKER_DATE;
                        break;

                    case 'time':
                        $type = FormDisplayStrategy::DATEPICKER_TIME;
                        break;

                    case 'datetime':
                    case 'timestamp':
                        $type = FormDisplayStrategy::DATEPICKER_DATETIME;
                        break;
                }
                break;

            case 'array':
            case 'json':
                $type = FormDisplayStrategy::TEXTAREA;
                break;

            // Special case: stapler file attachment
            case 'stapler-attachment':
                if ($data->type === 'image') {
                    $type = FormDisplayStrategy::ATTACHMENT_STAPLER_IMAGE;
                } else {
                    $type = FormDisplayStrategy::ATTACHMENT_STAPLER_FILE;
                }
                break;
        }

        return $type;
    }

    /**
     * Determines a form store display strategy string for given attribute data.
     *
     * @param ModelAttributeData $data
     * @return string|null
     */
    public function determineFormStoreStrategy(ModelAttributeData $data)
    {
        $type       = null;
        $parameters = [];

        // Determine strategy alias

        switch ($data->cast) {

            case 'boolean':
            case 'bool':
                $type = FormStoreStrategy::BOOLEAN;
                break;

            case 'date':
                $type = FormStoreStrategy::DATE;
                break;

            // Special case: stapler file attachment
            case 'stapler-attachment':
                $type = FormStoreStrategy::STAPLER;
                break;
        }

        // Determine parameters

        if ($data->translated) {
            $parameters[] = 'translated';
        }

        if ($data->nullable) {
            $parameters[] = 'nullable';
        }

        if (count($parameters)) {
            $type .= ':' . implode(',', $parameters);
        }

        return $type;
    }

}
