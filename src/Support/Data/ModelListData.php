<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelListDataInterface;

/**
 * Class ModelListInformation
 *
 * Data container that represents list representation for the model.
 *
 * @property int|array $page_size
 * @property array|ModelListColumnData[] $columns
 * @property bool $disable_filters
 * @property array|ModelListFilterData[] $filters
 * @property array|ModelIncludesData $includes
 * @property bool $disable_scopes
 * @property array|ModelScopeData[] $scopes
 * @property string|array $default_sort
 * @property bool $orderable
 * @property string $order_strategy
 * @property string $order_column
 * @property bool $activatable
 * @property string $active_column
 */
class ModelListData extends AbstractDataObject implements ModelListDataInterface
{

    protected $objects = [
        'columns'  => ModelListColumnData::class . '[]',
        'filters'  => ModelListFilterData::class . '[]',
        'includes' => ModelIncludesData::class,
        'scopes'   => ModelScopeData::class . '[]',
    ];

    protected $attributes = [

        // Arrays (instances of ModelListColumnData) with information about a single column.
        // These should appear in the order in which they should be displayed, and exclude standard/global list columns.
        // The entries should be keyed with an identifiying string that may be referred to by other list options.
        'columns' => [],

        // Whether to disable filters even if they're set
        'disable_filters' => false,
        // Arrays (instances of ModelListFilterData) with information about available filters in the listing.
        // These should appear in the order in which they should be displayed. They should be keyed by strings
        // that may be referred to in filter POST data.
        'filters' => [],

        // How the listing should be ordered by default
        // This value can also refer to an ordering strategy by name or <FQN>@<method> for custom ordering,
        // or consist of an array of such patterns.
        'default_sort' => null,

        // The (default) page size to use when showing the list.
        // If the page size should be variable, this can also by an array with integer values, the first of which
        // should be the default, so the user can manually switch them in the listing.
        'page_size' => null,

        // Whether the list may be manually ordered (f.i. by dragging and dropping records)
        'orderable' => null,
        // The strategy by which the model can be ordered. For now, this should always be 'listify'.
        'order_strategy' => 'listify',
        // The column used for the order strategy ('position' for listify)
        'order_column' => null,

        // Whether the model may be activated/deactived through the listing; ie. whether it has a manipulable 'active' flag.
        'activatable' => null,
        // The column that should be toggled when toggling 'active' status for the model.
        'active_column' => null,

        // Whether to disable the use and display of scopes.
        'disable_scopes' => null,
        // Scopes or scoping strategies, keyed by the scope name.
        'scopes' => [],
    ];

    /**
     * Returns the orderable (listify) column that should be used.
     *
     * @return string
     */
    public function getOrderableColumn()
    {
        return $this->order_column ?: 'position';
    }

    /**
     * @param ModelListDataInterface|ModelListData $with
     * @return $this
     */
    public function merge(ModelListDataInterface $with)
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

        // Overwrite filters if specifically set
        if ($with->filters && count($with->filters)) {
            $this->filters = $with->filters;
        }

        // Overwrite scopes if they are specifically set
        if ($with->scopes && count($with->scopes)) {
            $this->scopes = $with->scopes;
        }


        $standardMergeKeys = [
            'page_size',
            'orderable',
            'order_strategy',
            'order_column',
            'activatable',
            'activate_column',
            'default_sort',
            'disable_filters',
            'disable_scopes',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        return $this;
    }

}
