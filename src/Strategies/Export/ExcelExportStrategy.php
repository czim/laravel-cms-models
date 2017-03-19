<?php
namespace Czim\CmsModels\Strategies\Export;

use Carbon\Carbon;
use Czim\CmsModels\Contracts\ModelInformation\Data\Export\ModelExportColumnDataInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use RuntimeException;

/**
 * Class ExcelExportStrategy
 *
 * Requires maatwebsite/excel.
 */
class ExcelExportStrategy extends AbstractModelListExporter
{

    /**
     * Returns the extension to be used for files generated.
     *
     * @return string
     */
    public function extension()
    {
        return 'xls';
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

        if ( ! app()->bound('excel')) {
            throw new RuntimeException("Excel exporter strategy expected 'excel' to be bound for IoC");
        }

        /** @var Excel $excel */
        $excel = app('excel');

        $columns    = $this->exportInfo->columns;
        $strategies = $this->getColumnStrategyInstances();

        $excel
            ->create(pathinfo($temporary, PATHINFO_FILENAME), function ($excel) use ($columns, $strategies) {
                /** @var LaravelExcelWriter $excel */

                $excel->setTitle($this->getTitle());

                $excel->sheet($this->getWorksheetTitle(), function($sheet) use ($columns, $strategies) {
                    /** @var LaravelExcelWorksheet $sheet */

                    // Generate header row, if not disabled
                    if ($this->shouldHaveHeaderRow()) {

                        $headers = array_map(
                            function (ModelExportColumnDataInterface $column) {
                                return $column->header();
                            },
                            $columns
                        );

                        $sheet->appendRow($headers);
                        $sheet->freezeFirstRow();

                        // todo: style first row according to options
                    }

                    // For each query chunk: get a model instance,
                    // and for each column, generate the column content using the strategies
                    $this->query->chunk(
                        static::CHUNK_SIZE,
                        function ($records) use ($sheet, $columns, $strategies) {
                            /** @var Collection|Model[] $records */

                            foreach ($records as $record) {

                                $fields = [];

                                foreach ($strategies as $key => $strategy) {
                                    $fields[] = $strategy->render($record, $columns[ $key ]->source);
                                }

                                $sheet->appendRow($fields);

                                // todo: style row according to options
                            }
                        }
                    );
                });
            })
            ->store($this->extension(), pathinfo($temporary, PATHINFO_DIRNAME));

        return $temporary;
    }

    /**
     * Returns excel title.
     *
     * @return string
     */
    protected function getTitle()
    {
        return $this->getModelInformation()->labelPlural() . ' - ' . Carbon::now()->format('Y-m-d');
    }

    /**
     * Returns excel main worksheet title.
     *
     * @return string
     */
    protected function getWorksheetTitle()
    {
        return $this->getModelInformation()->labelPlural();
    }

    /**
     * Returns temporary file path to use while building export.
     *
     * @return string
     */
    protected function getTemporaryFilePath()
    {
        return storage_path(uniqid('excel-model-export') . '.' . $this->extension());
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

}
