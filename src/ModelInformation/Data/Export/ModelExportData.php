<?php
namespace Czim\CmsModels\ModelInformation\Data\Export;

use Czim\CmsModels\Contracts\ModelInformation\Data\Export\ModelExportDataInterface;
use Czim\CmsModels\ModelInformation\Data\AbstractModelInformationDataObject;

/**
 * Class ModelExportData
 *
 * Data container that represents list representation for the model.
 *
 * @property bool $enable
 * @property array|ModelExportColumnData[] $columns
 * @property array|ModelExportStrategyData[] $strategies
 */
class ModelExportData extends AbstractModelInformationDataObject implements ModelExportDataInterface
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

    protected $known = [
        'enable',
        'columns',
        'strategies',
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

                if ( ! array_has($this->columns, $key)) {
                    $mergedColumns[ $key ] = $data;
                    continue;
                }

                $this->columns[ $key ]->merge($data);
                $mergedColumns[ $key ] = $this->columns[ $key ];
            }

            $this->columns = $mergedColumns;
        }

        $standardMergeKeys = [
            'enable',
            'strategies',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        return $this;
    }

}
