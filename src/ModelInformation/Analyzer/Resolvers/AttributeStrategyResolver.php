<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Resolvers;

use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\ExportColumnStrategy;
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

            // Special case: paperclip file attachment
            case 'paperclip-attachment':
                if ($data->type === 'image') {
                    $type = ListDisplayStrategy::PAPERCLIP_THUMBNAIL;
                } else {
                    $type = ListDisplayStrategy::PAPERCLIP_FILENAME;
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
                        $type = FormDisplayStrategy::DROPDOWN;
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

            // Special case: paperclip file attachment
            case 'paperclip-attachment':
                if ($data->type === 'image') {
                    $type = FormDisplayStrategy::ATTACHMENT_PAPERCLIP_IMAGE;
                } else {
                    $type = FormDisplayStrategy::ATTACHMENT_PAPERCLIP_FILE;
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

            // Special case: paperclip file attachment
            case 'paperclip-attachment':
                $type = FormStoreStrategy::PAPERCLIP;
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

    /**
     * Determines form strategy options for given attribute data.
     *
     * @param ModelAttributeData $data
     * @return array
     */
    public function determineFormStoreOptions(ModelAttributeData $data)
    {
        $options = [];

        switch ($data->cast) {

            case 'bool':
            case 'boolean':
                $options['type'] = 'number';
                $options['min'] = 0;
                $options['max'] = 1;
                $options['size'] = 1;
                break;

            case 'integer':
            case 'int':
                $options['type'] = 'number';

                list($options['min'], $options['max']) = $this->getMinMaxForInteger(
                    $data->type,
                    $data->unsigned,
                    $data->length
                );

                if ($options['max'] < 1) {
                    // @codeCoverageIgnoreStart
                    array_forget($options, ['min','max']);
                    // @codeCoverageIgnoreEnd
                } else {
                    $options['size'] = strlen((string) round($options['max']));
                }
                break;

            case 'float':
            case 'double':
                $options['type'] = 'number';
                $options['step'] = 0.01;
                break;

            case 'string':
                $options['maxlength'] = $data->length;

                if ($data->type === 'enum') {
                    $options['values'] = $data->values ?: [];
                }
                break;

            // default omitted on purpose
        }

        return $options;
    }

    /**
     * Determines an export column strategy string for given attribute data.
     *
     * @param ModelAttributeData $data
     * @return string|null
     */
    public function determineExportColumnStrategy(ModelAttributeData $data)
    {
        $type = null;

        switch ($data->cast) {

            case 'boolean':
            case 'bool':
                $type = ExportColumnStrategy::BOOLEAN_STRING;
                break;

            case 'stapler-attachment':
                $type = ExportColumnStrategy::STAPLER_FILE_LINK;
                break;

            case 'paperclip-attachment':
                $type = ExportColumnStrategy::PAPERCLIP_FILE_LINK;
                break;

            case 'date':
                $type = ExportColumnStrategy::DATE;
                break;
        }

        return $type;
    }

    /**
     * Returns min & max value for a given integer attribute.
     *
     * @param string   $type
     * @param bool     $unsigned
     * @param int|null $length
     * @return array    [ min, max ]
     */
    protected function getMinMaxForInteger($type, $unsigned, $length = null)
    {
        $unsigned = (bool) $unsigned;

        switch ($type) {

            case 'tinyint':
                $bytes = 1;
                break;

            case 'mediumint':
                $bytes = 2;
                break;

            case 'int':
                $bytes = 4;
                break;

            case 'bigint':
                $bytes = 8;
                break;

            // @codeCoverageIgnoreStart
            default:
                // @codeCoverageIgnoreStart
                return [0, 0];
        }

        if ($unsigned) {
            return [ 0, pow(256, $bytes) - 1 ];
        }

        return [
            -1 * (pow(256, $bytes) / 2) - 1,
            pow(256, $bytes) / 2 - 1
        ];
    }
}
