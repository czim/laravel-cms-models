<?php
namespace Czim\CmsModels\Strategies\Export;

use Czim\CmsModels\Contracts\ModelInformation\Data\Export\ModelExportColumnDataInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

class CsvExportStrategy extends AbstractModelListExporter
{

    /**
     * Returns the extension to be used for files generated.
     *
     * @return string
     */
    public function extension()
    {
        return 'csv';
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

        // Create new csv file
        $resource = fopen($temporary, 'w');

        if (false === $resource) {
            throw new RuntimeException("Failed to open temporary export file '{$temporary}'");
        }

        try {
            $this->writeCsvContent($resource);

        } catch (Exception $e) {

            throw $e;

        } finally {

            fclose($resource);
        }

        return $temporary;
    }

    /**
     * Writes CSV content to given file handle resource.
     *
     * @param resource $resource
     */
    protected function writeCsvContent($resource)
    {
        $columns    = $this->exportInfo->columns;
        $strategies = $this->getColumnStrategyInstances();

        $delimiter = $this->getDelimiterSymbol();
        $enclosure = $this->getEnclosureSymbol();
        $escape    = $this->getEscapeSymbol();


        // Generate header row, if not disabled
        if ($this->shouldHaveHeaderRow()) {

            $headers = array_map(
                function (ModelExportColumnDataInterface $column) {
                    return $column->header();
                },
                $columns
            );

            fputcsv($resource, $headers, $delimiter, $enclosure, $escape);
        }

        // For each query chunk: get a model instance,
        // and for each column, generate the column content using the strategies
        $this->query->chunk(
            static::CHUNK_SIZE,
            function ($records) use ($resource, $columns, $strategies, $delimiter, $enclosure, $escape) {
                /** @var Collection|Model[] $records */

                foreach ($records as $record) {

                    $fields = [];

                    foreach ($strategies as $key => $strategy) {
                        $fields[] = $strategy->render($record, $columns[$key]->source);
                    }

                    fputcsv($resource, $fields, $delimiter, $enclosure, $escape);
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
        return storage_path(uniqid('csv-model-export') . '.csv');
    }

    /**
     * Returns whether document should have a header row.
     *
     * @return bool
     */
    protected function shouldHaveHeaderRow()
    {
        return ! (bool) array_get($this->exportInfo->options(), 'no_header_row', false);
    }

    /**
     * Returns CSV delimiter symbol.
     *
     * @return string
     */
    protected function getDelimiterSymbol()
    {
        return ';';
    }

    /**
     * Returns CSV value enclosure symbol.
     *
     * @return string
     */
    protected function getEnclosureSymbol()
    {
        return '"';
    }

    /**
     * Returns CSV escape symbol.
     *
     * @return string
     */
    protected function getEscapeSymbol()
    {
        return '\\';
    }

}
