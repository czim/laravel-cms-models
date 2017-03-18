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

}
