<?php
namespace Czim\CmsModels\Analyzer\Database;

use DB;

class SqliteDatabaseAnalyzer extends AbstractDatabaseAnalyzer
{

    /**
     * Returns column information for a given table.
     *
     * @param string      $table
     * @param string|null $connection   optional connection name
     * @return array
     */
    public function getColumns($table, $connection = null)
    {
        $this->updateConnection($connection)->setUpDoctrineSchema();

        $columns = $this->getParsedColumnTable($table);

        $columnData = [];

        foreach ($columns as $name => $column) {

            list($type, $length) = $this->normalizeTypeAndLength(array_get($column, 'type'));

            $columnData[] = [
                'name'     => $name,
                'type'     => $type,
                'length'   => $length,
                'values'   => false,
                'unsigned' => false,
                'nullable' => ! (bool) array_get($column, 'notnull', false),
            ];
        }

        return $columnData;
    }

    /**
     * Returns a parsed set of columns for a table.
     *
     * @param string $table
     * @return array associative, keyed by column name
     */
    protected function getParsedColumnTable($table)
    {
        $this->validateTableName($table);

        $table = DB::connection($this->connection)->select(
            DB::connection($this->connection)->raw("PRAGMA table_info({$table});")
        );

        $columns = [];

        foreach ($table as $columnData) {
            $columns[ $columnData->name ] = (array) $columnData;
        }

        return $columns;
    }

    /**
     * Returns clean type string and length value.
     *
     * @param string $type  sqlite type
     * @return array    [ type, length (int) ]
     */
    protected function normalizeTypeAndLength($type)
    {
        if (empty($type)) {
            // @codeCoverageIgnoreStart
            return [null, null];
            // @codeCoverageIgnoreEnd
        }

        if (preg_match('#^(?<type>.*)\((?<length>\d+)\)$#', $type, $matches)) {
            return [ $this->normalizeType($matches['type']), (int) $matches['length'] ];
        }

        return [ $this->normalizeType($type), $this->getDefaultLengthForType($type) ];
    }

    /**
     * Returns sensible default length for column type.
     *
     * @param string $type  sqlite type (without length)
     * @return int|null
     */
    protected function getDefaultLengthForType($type)
    {
        switch ($type) {

            case 'integer':
                return 8;

            case 'varchar':
                return 255;
        }

        return null;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function normalizeType($type)
    {
        switch ($type) {

            case 'numeric':
                return 'decimal';
        }

        return $type;
    }

}
