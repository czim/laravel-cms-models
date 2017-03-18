<?php
namespace Czim\CmsModels\Analyzer\Database;

use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use DB;

class AbstractDatabaseAnalyzer implements DatabaseAnalyzerInterface
{

    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function getSchemaBuilder()
    {
        return DB::getSchemaBuilder();
    }

    /**
     * Returns column information for a given table.
     *
     * @param string $table
     * @return array
     */
    public function getColumns($table)
    {
        $schema  = $this->getSchemaBuilder();
        $columns = $schema->getColumnListing($table);

        $columnData = [];

        foreach ($columns as $name) {

            $column = $schema->getConnection()->getDoctrineColumn($table, $name);

            $columnData[] = [
                'name'     => $column->getName(),
                'type'     => $column->getType()->getName(),
                'length'   => $column->getLength(),
                'values'   => false,
                'unsigned' => $column->getUnsigned(),
                'nullable' => ! $column->getNotnull(),
            ];
        }

        return $columnData;
    }

    /**
     * Checks and throws exception if table name is unsafe to inject.
     *
     * @param string $table
     */
    protected function validateTableName($table)
    {
        if ( ! preg_match('#^[a-z0-9_-]*$#i', $table)) {
            throw new \InvalidArgumentException("Unsafe table name: '{$table}'");
        }
    }

    /**
     * Checks and throws exception if column name is unsafe to inject.
     *
     * @param string $column
     * @codeCoverageIgnore
     */
    protected function validateColumnName($column)
    {
        if ( ! preg_match('#^[a-z0-9_-]*$#i', $column)) {
            throw new \InvalidArgumentException("Unsafe table column: '{$column}'");
        }
    }

}
