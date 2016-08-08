<?php
namespace Czim\CmsModels\Contracts\Analyzer;

interface DatabaseAnalyzerInterface
{

    /**
     * Returns column information for a given table.
     *
     * @param $table
     * @return array    associative, with column information
     */
    public function getColumns($table);

    /**
     * Returns column type.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public function getColumnType($table, $column);

}
