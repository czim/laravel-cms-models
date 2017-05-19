<?php
namespace Czim\CmsModels\Strategies\Export;

use Czim\CmsModels\Contracts\ModelInformation\Data\Export\ModelExportColumnDataInterface;
use Czim\CmsModels\Contracts\Strategies\Export\ExportColumnInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SoapBox\Formatter\Formatter;

/**
 * Class XmlExportStrategy
 *
 * Requires soapbox/laravel-formatter.
 */
class XmlExportStrategy extends AbstractModelListExporter
{
    /**
     * @var string[]
     */
    protected $columns = [];

    /**
     * @var ExportColumnInterface[]
     */
    protected $columnStrategies = [];

    /**
     * @var string[]
     */
    protected $columnHeaders = [];


    /**
     * Returns the extension to be used for files generated.
     *
     * @return string
     */
    public function extension()
    {
        return 'xml';
    }

    /**
     * Generates an export, download or local file.
     *
     * @param null|string $path
     * @return mixed
     * @throws Exception
     */
    protected function generateExport($path = null)
    {
        $temporary = $this->getTemporaryFilePath();

        // Prepare columns/strategies
        $this->columns          = $this->exportInfo->columns;
        $this->columnStrategies = $this->getColumnStrategyInstances();

        // Prepare headers/field keys
        $this->columnHeaders = array_map(
            function (ModelExportColumnDataInterface $column) {
                return $column->header();
            },
            $this->columns
        );

        // Create data array
        $data = [];
        $this->fillDataArray($data);

        $formatter = Formatter::make($data, Formatter::ARR);

        file_put_contents($temporary, $formatter->toXml());

        return $temporary;
    }

    /**
     * Fills a data array with entries of key/value pairs ready for exporting.
     *
     * @param array $data       by reference
     */
    protected function fillDataArray(array &$data)
    {
        $this->query->chunk(
            static::CHUNK_SIZE,
            function ($records) use (&$data) {
                /** @var Collection|Model[] $records */

                foreach ($records as $record) {

                    $fields = [];

                    foreach ($this->columnStrategies as $key => $strategy) {
                        $fields[] = $strategy->render($record, $this->columns[$key]->source);
                    }

                    $data[] = array_combine($this->columnHeaders, $fields);
                }
            }
        );
    }

    /**
     * Returns temporary file path to use while building export.
     *
     * @return string
     */
    protected function getTemporaryFilePath()
    {
        return storage_path(uniqid('xml-model-export') . '.xml');
    }

}
