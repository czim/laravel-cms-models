<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;

/**
 * Class ModelInformation
 *
 * Data container that represents model information for the model module generator.
 *
 * @property string $model
 * @property string $original_model
 * @property array|ModelMetaData $meta
 * @property string $verbose_name
 * @property string $verbose_name_plural
 * @property bool $single
 * @property bool $allow_delete
 * @property mixed $delete_condition
 * @property array|ModelReferenceData $reference
 * @property bool $incrementing
 * @property bool $timestamps
 * @property string $timestamp_created
 * @property string $timestamp_updated
 * @property bool $translated
 * @property string $translation_strategy
 * @property array|ModelIncludesData $includes
 * @property array|ModelAttributeData[] $attributes
 * @property array|ModelRelationData[] $relations
 * @property array|ModelListData $list
 * @property array|ModelFormData $form
 * @property array $validation
 * @property array $export
 */
class ModelInformation extends AbstractDataObject implements ModelInformationInterface
{

    protected $objects = [
        'meta'       => ModelMetaData::class,
        'includes'   => ModelIncludesData::class,
        'reference'  => ModelReferenceData::class,
        'list'       => ModelListData::class,
        'form'       => ModelFormData::class,
        'attributes' => ModelAttributeData::class . '[]',
        'relations'  => ModelRelationData::class . '[]',
    ];

    protected $attributes = [

        // FQN of the model to use for saving the Model's data
        'model' => null,
        // FQN Even if model_class isn't this should always be the original Model described
        'original_model' => null,

        // Instance of ModelMetaData
        'meta' => [
            // FQN for the controller class to handle the model's web & API presence
            'controller' => null,
            // Default controller action to link to for the basic model's menu presence ('index', 'create', for instance)
            'default_controller_method' => null,

            // The strategy to apply to the base repository query for listings & accessibility of models.
            'repository_strategy' => null,

            // Whether to disable all global scopes (boolean true) or a string with comma-separated global scopes to disable
            'disable_global_scopes' => null,

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

        // Information for external references of this model, ModelReferenceData
        'reference' => [
            'strategy' => null,
            'source'   => null,
            'search'   => null,
        ],

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

        // Includes for model eager loading, instance of ModelIncludesData
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


        // Settings for rendering the index/listing of records for this model, ModelListData
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

            // Whether to disable the use and display of scopes.
            'disable_scopes' => false,
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

    /**
     * Merges information into this information set, with the new information being leading.
     *
     * @param ModelInformationInterface|ModelInformation $with
     */
    public function merge(ModelInformationInterface $with)
    {
        if ( ! empty($with->model)) {
            $this->model = $with->model;
        }

        if ( ! empty($with->original_model)) {
            $this->original_model = $with->original_model;
        }

        // Meta information

        $this->mergeAttribute('meta', $with->meta);
        $this->mergeAttribute('verbose_name', $with->verbose_name);
        $this->mergeAttribute('verbose_name_plural', $with->verbose_name_plural);

        $this->mergeAttribute('list', $with->list);
        $this->mergeAttribute('reference', $with->reference);

    }

}
