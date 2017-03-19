<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Analyzer;

interface DatabaseAnalyzerInterface
{

    /**
     * Returns column information for a given table.
     *
     * @param string      $table
     * @param string|null $connection   optional connection name
     * @return array
     */
    public function getColumns($table, $connection = null);

}
