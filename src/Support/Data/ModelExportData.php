<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelExportDataInterface;

/**
 * Class ModelExportData
 *
 * Data container that represents list representation for the model.
 *
 * @property bool $enabled
 * @property array|ModelExportColumnData[] $columns
 * @property array|ModelExportStrategyData[] $strategies
 */
class ModelExportData extends AbstractDataObject implements ModelExportDataInterface
{

    protected $objects = [
        'columns'    => ModelExportColumnData::class . '[]',
        'strategies' => ModelExportStrategyData::class . '[]',
    ];

    protected $attributes = [

        // Whether to allow exporting at all
        'enable' => false,

        // Default columns to include for every export strategy that does not override them.
        // Arrays (instances of ModelExportColumnData) with information about a single column.
        // All columns that should be present in the export, should be listed here, in the right order.
        'columns' => [],

        // Strategies for exporting: csv, excel, xml
        'strategies' => [],
    ];

    /**
     * @param ModelExportDataInterface|ModelExportData $with
     * @return $this
     */
    public function merge(ModelExportDataInterface $with)
    {
        // Overwrite columns intelligently: keep only the columns for keys that were set
        // and merge those for which data is set.
        if ($with->columns && count($with->columns)) {

            $mergedColumns = [];

            foreach ($with->columns as $key => $data) {

                if (array_has($this->columns, $key)) {
                    $data = $this->columns[ $key ]->merge($data);
                }

                $mergedColumns[ $key ] = $data;
            }

            $this->columns = $mergedColumns;
        }

        $standardMergeKeys = [
            'enabled',
            'strategies',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        return $this;
    }

}
