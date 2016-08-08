<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\DataObject\AbstractDataObject;

/**
 * Class ModelInformation
 *
 * Data container that represents model information for the model module generator.
 *
 * @property string $model_class
 */
class ModelInformation extends AbstractDataObject implements ModelInformationInterface
{

    protected $attributes = [

        // FQN of the model to use for saving the Model's data
        'model' => null,
        // FQN Even if model_class isn't this should always be the original Model described
        'original_model' => null,

        'meta' => [
            // FQN for the controller class to handle the model's web & API presence
            'controller' => null,
            // Default controller action to link to for the basic model's menu presence ('index', 'create', for instance)
            'default_controller_method' => 'index',

            // List of FQNs for form requests, keyed by the relevant POST controller method name ('update', 'create')
            'form_requests' => [],

            // List of (default) views to use, keyed by the controller action method name.
            'views' => [],

            // API serialization transformer class to use. Any class that implements the models module transformer interface.
            'transformer' => null,
        ],


        // Display name for the model (or translation key)
        // Defaults to: model class name
        'verbose_name' => null,
        // Plural display name for the model (or translation key)
        // Defaults to: model class name, pluralized
        'verbose_name_plural' => null,

        // Whether the model will always only have exactly one record
        'single' => false,

        // Whether to allow deletion at all (even with permissions set)
        'allow_delete' => true,
        // The strategy for allowing deletion (such as not being linked to from other models, etc)
        // Use <FQN>@<method> format to indicate a strategy
        'delete_condition' => null,

        // Column or strategy that defines a display value when referencing records from other forms/lists
        'reference' => null,

        // Whether the key is auto incrementing
        'incrementing' => true,

        // Whether the model has timestamps enabled
        'timestamps'        => false,
        'timestamp_created' => 'created_at',
        'timestamp_updated' => 'updated_at',

        // Whether the model uses Translatable (or a compatible translation strategy)
        'translated' => false,
        // The strategy by which the model is translated. For now, this should always be 'translatable'.
        'translation_strategy' => 'translatable',

        // Includes for model eager loading
        'includes' => [
            // List of default includes to use for loading a model
            'default' => [],
            // List of available includes to allow (either relation name string, or relation name key string => callable strategy)
            'available' => [],
        ],


        // Attributes: instances of ModelAttributeData
        'attributes' => [],

        // Relationships: instances of ModelRelationData
        'relations' => [],


        // Settings for rendering the index/listing of records for this model
        'list' => [
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

            // Includes for listing (overrides normal includes, if set)
            'includes' => [
                // List of default includes to use for loading models in the listing
                'default' => [],
                // List of available includes to allow (either relation name string, or relation name key string => callable strategy)
                'available' => [],
            ],
        ],

        // Settings for rendering the form by which the model is edited
        'form' => [

            // Arrays (instances of ModelFormFieldData or ModelFormFieldGroupData) that define the editable fields for
            // the model's form in the order in which they should appear.
            'fields' => [],
        ],

        // Settings for validation of submitted data.
        'validation' => [
            // Validation rules or methods that generate them, when creating a record.
            'create' => [],
            // Validation rules or methods that generate them, when updating a record.
            // If null, will default to create validation rules.
            'update' => null,
        ],

        // Settings for building an export of the listing of records for this model.
        // If no export data is set, its column properties will be based on the 'list' data.
        'export' => [
            // Whether to allow exporting at all
            'enable' => false,
            // FQN for the exporter class that should build the export
            'handler' => null,

            // Arrays (instances of ModelListColumnData) with information about a single column.
            // All columns that should be present in the export, should be listed here, in the right order.
            'columns' => [],
        ],

    ];


    /**
     * @return string
     */
    public function modelClass()
    {
        return $this->getAttribute('original_model');
    }

    /**
     * @return string
     */
    public function label()
    {
        return $this->getAttribute('verbose_name');
    }

    /**
     * @return string
     */
    public function labelPlural()
    {
        return $this->getAttribute('verbose_name_plural');
    }

}
