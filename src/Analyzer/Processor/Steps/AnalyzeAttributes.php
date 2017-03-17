<?php
namespace Czim\CmsModels\Analyzer\Processor\Steps;

use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\AttributeCast;

class AnalyzeAttributes extends AbstractAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        $attributes = [];

        // Get the columns from the model's table
        $tableFields = $this->databaseAnalyzer()->getColumns(
            $this->model()->getTable()
        );

        foreach ($tableFields as $field) {

            $length = $field['length'];

            $cast = $this->getAttributeCastForColumnType($field['type'], $length);

            $attributes[ $field['name'] ] = new ModelAttributeData([
                'name'     => $field['name'],
                'cast'     => $cast,
                'type'     => $field['type'],
                'nullable' => $field['nullable'],
                'unsigned' => $field['unsigned'],
                'length'   => $length,
                'values'   => $field['values'],
            ]);
        }


        foreach ($this->model()->getFillable() as $attribute) {

            if ( ! isset($attributes[ $attribute ])) {
                continue;
            }

            $attributes[ $attribute ]['fillable'] = true;
        }

        foreach ($this->model()->getCasts() as $attribute => $cast) {

            if ( ! isset($attributes[ $attribute ])) {
                continue;
            }

            $attributes[ $attribute ]['cast'] = $this->normalizeCastString($cast);
        }


        $this->info->attributes = $attributes;
    }

    /**
     * Returns cast enum value for database column type string and length.
     *
     * @param string   $type
     * @param null|int $length
     * @return string
     */
    protected function getAttributeCastForColumnType($type, $length = null)
    {
        switch ($type) {

            case 'bool':
                return AttributeCast::BOOLEAN;

            case 'tinyint':
                if ($length === 1) {
                    return AttributeCast::BOOLEAN;
                }
                return AttributeCast::INTEGER;

            case 'int':
            case 'integer':
            case 'mediumint':
            case 'smallint':
            case 'bigint':
                return AttributeCast::INTEGER;

            case 'dec':
            case 'decimal':
            case 'double':
            case 'float':
            case 'real':
                return AttributeCast::FLOAT;

            case 'varchar':
            case 'char':
            case 'enum':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'tinytext':
            case 'year':
            case 'blob';
            case 'mediumblob';
            case 'longblob';
            case 'binary';
            case 'varbinary';
                return AttributeCast::STRING;

            case 'date':
            case 'datetime':
            case 'time':
            case 'timestamp':
                return AttributeCast::DATE;

            default:
                return $type;
        }
    }

    /**
     * Normalizes a cast string to enum value if possible.
     *
     * @param string $cast
     * @return string
     */
    protected function normalizeCastString($cast)
    {
        switch ($cast) {

            case 'bool':
            case 'boolean':
                $cast = AttributeCast::BOOLEAN;
                break;

            case 'decimal':
            case 'double':
            case 'float':
            case 'real':
                $cast = AttributeCast::FLOAT;
                break;

            case 'int':
            case 'integer':
                $cast = AttributeCast::INTEGER;
                break;

            case 'date':
            case 'datetime':
                $cast = AttributeCast::DATE;
                break;
        }

        return $cast;
    }

}
