<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Database;

use Czim\CmsModels\Contracts\ModelInformation\Analyzer\DatabaseAnalyzerInterface;
use DB;

class AbstractDatabaseAnalyzer implements DatabaseAnalyzerInterface
{

    /**
     * The last known connection
     *
     * @var string|null
     */
    protected $connection;

    /**
     * Whether the doctrine enum mapping has been set up.
     *
     * @var bool
     */
    protected $schemaSetUp = false;


    /**
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function getSchemaBuilder()
    {
        return DB::connection($this->connection)->getSchemaBuilder();
    }

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

    /**
     * Updates the connection name to use.
     *
     * @param null|string $connection
     * @return $this
     */
    protected function updateConnection($connection)
    {
        if ($this->connection !== $connection) {
            $this->connection  = $connection;
            $this->schemaSetUp = false;
        }

        return $this;
    }

    /**
     * Sets up the schema for the current connection.
     */
    protected function setUpDoctrineSchema()
    {
        if ($this->schemaSetUp) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        DB::connection($this->connection)
            ->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        $this->schemaSetUp = true;
    }

}
