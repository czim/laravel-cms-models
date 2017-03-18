<?php
namespace Czim\CmsModels\Analyzer\Database;

use DB;

class MysqlDatabaseAnalyzer extends AbstractDatabaseAnalyzer
{

    /**
     * Returns column information for a given table.
     *
     * @param string $table
     * @return array
     */
    public function getColumns($table)
    {
        $this->validateTableName($table);

        $columns = DB::select(
            DB::raw("show columns from `{$table}`")
        );

        $columnData = [];

        foreach ($columns as $column) {

            $columnData[] = [
                'name'     => $column->Field,
                'type'     => $this->getColumnBaseTypeFromType($column->Type),
                'length'   => $this->getColumnLengthFromType($column->Type),
                'values'   => $this->getEnumValuesFromType($column->Type),
                'unsigned' => $this->getColumnIsNullableFromType($column->Type),
                'nullable' => ! preg_match('#^\s*no\s*$#i', $column->Null),
            ];
        }

        return $columnData;
    }

    /**
     * Returns base type for the column.
     *
     * @param string $type
     * @return bool|string
     */
    protected function getColumnBaseTypeFromType($type)
    {
        if ( ! preg_match('#(?<type>[^(]+)#', $type, $matches)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return strtolower($matches['type']);
    }

    /**
     * Returns whether the column is nullable.
     *
     * @param string $type
     * @return bool
     */
    protected function getColumnIsNullableFromType($type)
    {
        return (bool) preg_match('#\s+unsigned#', $type);
    }

    /**
     * Returns length parameter for the column type.
     *
     * @param string $type
     * @return bool|int
     */
    protected function getColumnLengthFromType($type)
    {
        if ( ! preg_match('#\((?<length>\d+)\)#', $type, $matches)) {
            return null;
        }

        return (int) $matches['length'];
    }

    /**
     * Returns enum values for column type string.
     *
     * @param string $type
     * @return array|bool
     */
    protected function getEnumValuesFromType($type)
    {
        if ( ! preg_match('#^enum\((?<values>.*)\)$#i', $type, $matches)) {
            return false;
        }

        $enum = [];

        foreach (explode(',', $matches['values']) as $value) {
            $v      = trim( $value, "'" );
            $enum[] = $v;
        }

        return $enum;
    }

}
