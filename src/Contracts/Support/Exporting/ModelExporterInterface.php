<?php
namespace Czim\CmsModels\Contracts\Support\Exporting;

use Czim\CmsModels\Contracts\Data\ModelExportStrategyDataInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface ModelExporterInterface
{

    /**
     * Sets strategy information to use while exporting.
     *
     * @param ModelExportStrategyDataInterface $exportInfo
     * @return $this
     */
    public function setStrategyData(ModelExportStrategyDataInterface $exportInfo);

    /**
     * Exports data returned by a model listing query to a file.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param string                       $path        full path to save the output to
     * @return string|false     full path string if successful
     */
    public function export($query, $path);

    /**
     * Exports data returned by listing query and returns a download response for it.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param string                       $filename    name the download should be given
     * @return mixed|false
     */
    public function download($query, $filename);

    /**
     * Returns the extension to be used for files generated.
     *
     * @return string
     */
    public function extension();

}
