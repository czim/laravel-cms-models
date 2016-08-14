<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class ModelListInformation
 *
 * Data container that represents list representation for the model.
 */
class ModelListData extends AbstractDataObject
{

    protected $objects = [
        'columns'  => ModelListColumnData::class . '[]',
        'filters'  => ModelListFilterData::class . '[]',
        'includes' => ModelMetaData::class,
    ];

    protected $attributes = [

        // Arrays (instances of ModelListColumnData) with information about a single column.
        // These should appear in the order in which they should be displayed, and exclude standard/global list columns.
        // The entries should be keyed with an identifiying string that may be referred to by other list options.
        'columns' => [],

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
        'orderable' => false,
        // The strategy by which the model can be ordered. For now, this should always be 'listify'.
        'order_strategy' => 'listify',

        // Whether the model may be activated/deactived through the listing; ie. whether it has a manipulable 'active' flag.
        'activated' => false,
        // The column that should be toggled when toggling 'active' status for the model.
        'active_column' => 'active',

        // Scopes or scoping strategies to present for getting selected sets of the model's records
        'scopes' => [],

        // Includes for listing (overrides normal includes, if set), MetaIncludesData instance
        'includes' => [

            // List of default includes to use for loading models in the listing
            'default' => [],
            // List of available includes to allow (either relation name string, or relation name key string => callable strategy)
            'available' => [],

        ],

    ];

}
