<?php
namespace Czim\CmsModels\Strategies\Export;

use Czim\CmsModels\Contracts\Data\ModelExportStrategyDataInterface;
use Czim\CmsModels\Contracts\Repositories\CurrentModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Support\Exporting\ExportColumnInterface;
use Czim\CmsModels\Contracts\Support\Exporting\ModelExporterInterface;
use Czim\CmsModels\Contracts\Support\Factories\ExportColumnStrategyFactoryInterface;
use Czim\CmsModels\Support\Data\ModelExportStrategyData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UnexpectedValueException;

abstract class AbstractModelListExporter implements ModelExporterInterface
{
    /**
     * Size of query chunks to use.
     *
     * @var int
     */
    const CHUNK_SIZE = 250;


    /**
     * @var EloquentBuilder|QueryBuilder
     */
    protected $query;

    /**
     * @var ModelExportStrategyDataInterface|ModelExportStrategyData
     */
    protected $exportInfo;

    /**
     * @var string
     */
    protected $modelClass;


    /**
     * Sets strategy information to use while exporting.
     *
     * @param ModelExportStrategyDataInterface $exportInfo
     * @return $this
     */
    public function setStrategyData(ModelExportStrategyDataInterface $exportInfo)
    {
        $this->exportInfo = $exportInfo;

        return $this;
    }

    /**
     * Exports data returned by a model listing query to a file.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param string                       $path full path to save the output to
     * @return string|false     full path string if successful
     */
    public function export($query, $path)
    {
        $this->query      = $query;
        $this->modelClass = get_class($query->getModel());

        return $this->generateExport($path);
    }

    /**
     * Exports data returned by listing query and returns a download response for it.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param string                       $filename name the download should be given
     * @return mixed|false
     */
    public function download($query, $filename)
    {
        $this->query      = $query;
        $this->modelClass = get_class($query->getModel());

        $temporary = $this->generateExport();

        if ( ! $temporary) {
            return false;
        }

        return response()->download($temporary, $filename)->deleteFileAfterSend(true);
    }


    /**
     * Generates an export, download or local file.
     *
     * @param null|string $path
     * @return mixed
     */
    abstract protected function generateExport($path = null);


    /**
     * Returns list of export column strategy instances in order.
     *
     * @return ExportColumnInterface[]  keyed by export column key
     */
    protected function getColumnStrategyInstances()
    {
        if ( ! $this->exportInfo) {
            throw new UnexpectedValueException("No export information set, cannot determine column strategies");
        }

        $information = $this->getModelInformation();

        $factory = $this->getExportColumnStrategyFactory();

        $instances = [];

        foreach ($this->exportInfo->columns as $key => $columnData) {

            $instance = $factory->make($columnData->strategy);

            $instance->setColumnInformation($columnData);

            if ($columnData->source) {
                if (isset($information->attributes[ $columnData->source ])) {
                    $instance->setAttributeInformation(
                        $information->attributes[ $columnData->source ]
                    );
                }
            }

            $instances[ $key ] = $instance->initialize($this->modelClass);
        }

        if ( ! count($instances)) {
            throw new UnexpectedValueException("No strategies for any columns, cannot perform export");
        }

        return $instances;
    }

    /**
     * @return ExportColumnStrategyFactoryInterface
     */
    protected function getExportColumnStrategyFactory()
    {
        return app(ExportColumnStrategyFactoryInterface::class);
    }

    /**
     * @return ModelInformation|false
     */
    protected function getModelInformation()
    {
        if ( ! $this->modelClass) {
            /** @var CurrentModelInformationInterface $current */
            $current = app(CurrentModelInformationInterface::class);

            $this->modelClass = $current->forModel();
        }

        /** @var ModelInformationRepositoryInterface $repository */
        $repository = app(ModelInformationRepositoryInterface::class);

        return $repository->getByModelClass($this->modelClass);
    }

}
