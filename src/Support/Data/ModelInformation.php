<?php
namespace Czim\CmsModels\Support\Data;

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
 * @property string $translated_name
 * @property string $translated_name_plural
 * @property bool $single
 * @property bool $allow_delete
 * @property mixed $delete_condition
 * @property string $delete_strategy
 * @property bool $confirm_delete
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
 * @property array|ModelShowData $show
 * @property array|ModelExportData $export
 */
class ModelInformation extends AbstractModelInformationDataObject implements ModelInformationInterface
{

    protected $objects = [
        'meta'       => ModelMetaData::class,
        'includes'   => ModelIncludesData::class,
        'reference'  => ModelReferenceData::class,
        'list'       => ModelListData::class,
        'form'       => ModelFormData::class,
        'show'       => ModelShowData::class,
        'export'     => ModelExportData::class,
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

            // FQN for the controller class to handle the model's web presence
            'controller' => null,
            // Default controller action to link to for the basic model's menu presence ('index', 'create', for instance)
            'default_controller_method' => null,

            // FQN for the controller class to handle the model's API presence
            'controller_api' => null,

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
        'verbose_name'    => null,
        'translated_name' => null,
        // Plural display name for the model (or translation key)
        // Defaults to: model class name, pluralized
        'verbose_name_plural'    => null,
        'translated_name_plural' => null,

        // Whether the model will always only have exactly one record
        'single' => false,

        // Whether to allow deletion at all (even with permissions set)
        'allow_delete' => null,
        // The strategy for allowing deletion (such as not being linked to from other models, etc)
        // Use <FQN> format to indicate a strategy class, or use an alias
        // Parameters may be set per strategy. When using multiple strategies, separate them with pipes.
        // Ex.: strategy1:param,param2|strategy2:param
        'delete_condition' => null,
        // The strategy for performing deletion.
        'delete_strategy' => null,
        // Whether deletion should be confirmed by the user as safeguard.
        'confirm_delete' => null,

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

            // Whether to hide everything but top-level list parents by default, and if so, using what relation.
            // Useful to remove clutter for nested content with a click-through-to-children setup.
            // Set to relation method name that should be present in 'parents'.
            'default_top_relation' => null,

            // List parents for list hierarchy handling (instances of ModelListParentData)
            'parents' => [],
        ],

        // Settings for rendering the form by which the model is edited
        'form' => [

            // The layout of the form fields
            // Tabs, Fieldsets and keys for fields (in the order they should appear).
            // Tabs and Fieldsets should be keyed by references to use for them.
            // If not set, simply shows fields in the order they are defined.
            'layout' => null,

            // Arrays (instances of ModelFormFieldData or ModelFormFieldGroupData) that define the editable fields for
            // the model's form in the order in which they should appear by default.
            'fields' => [],

            // Settings for validation of submitted data.
            'validation' => [
                // Validation rules, when creating a record.
                'create' => [],

                // Validation rules, when updating a record.
                // If null, will default to create validation rules.
                'update' => null,

                // If true, will replace default create rules set under 'create' entirely.
                'create_replace' => null,
                // If true, will replace default update rules set under 'update' entirely.
                'update_replace' => null,
            ],
        ],

        // Settings for rendering the show model page
        'show' => [

            // Arrays (instances of ModelShowFieldData) that define the fields to be displayed for
            // the model's show page in the order in which they should appear.
            'fields' => [],
        ],

        // Settings for building an export of the listing of records for this model.
        // If no export data is set, its column properties will be based on the 'list' data.
        'export' => [

            // Whether to allow exporting at all
            'enable' => false,

            // Default columns to include for every export strategy that does not override them.
            // Arrays (instances of ModelExportColumnData) with information about a single column.
            // All columns that should be present in the export, should be listed here, in the right order.
            'columns' => [],

            // Strategies for exporting: csv, excel, xml
            'strategies' => [],
        ],
    ];

    protected $known = [
        'model',
        'original_model',
        'meta',
        'includes',
        'reference',
        'list',
        'form',
        'show',
        'export',
        'attributes',
        'relations',
        'verbose_name',
        'translated_name',
        'verbose_name_plural',
        'translated_name_plural',
        'single',
        'allow_delete',
        'delete_condition',
        'delete_strategy',
        'confirm_delete',
        'incrementing',
        'timestamps',
        'timestamp_created',
        'timestamp_updated',
        'translated',
        'translation_strategy',
    ];


    /**
     * @return string
     */
    public function modelClass()
    {
        return $this->getAttribute('original_model');
    }

    /**
     * Returns label for single item.
     *
     * @param bool $translated  return translated if possible
     * @return string
     */
    public function label($translated = true)
    {
        if ($translated && $key = $this->getAttribute('translated_name')) {
            if (($label = cms_trans($key)) !== $key) {
                return $label;
            }
        }

        return $this->getAttribute('verbose_name');
    }

    /**
     * Returns translation key for label for single item.
     *
     * @return string
     */
    public function labelTranslationKey()
    {
        return $this->getAttribute('translated_name');
    }

    /**
     * Returns label for multiple items.
     *
     * @param bool $translated  return translated if possible
     * @return string
     */
    public function labelPlural($translated = true)
    {
        if ($translated && $key = $this->getAttribute('translated_name_plural')) {
            if (($label = cms_trans($key)) !== $key) {
                return $label;
            }
        }

        return $this->getAttribute('verbose_name_plural');
    }

    /**
     * Returns translation key for label for multiple items.
     *
     * @return string
     */
    public function labelPluralTranslationKey()
    {
        return $this->getAttribute('translated_name_plural');
    }

    /**
     * Returns whether the model may be deleted at all.
     *
     * @return bool
     */
    public function allowDelete()
    {
        if (null === $this->allow_delete) {
            return true;
        }

        return (bool) $this->allow_delete;
    }

    /**
     * Returns delete condition if set, or false if not.
     *
     * @return string|string[]|false
     */
    public function deleteCondition()
    {
        if (null === $this->delete_condition || false === $this->delete_condition) {
            return false;
        }

        return $this->delete_condition;
    }

    /**
     * Returns delete strategy if set, or false if not.
     *
     * @return string|false
     */
    public function deleteStrategy()
    {
        if (null === $this->delete_strategy || false === $this->delete_strategy) {
            return false;
        }

        return $this->delete_strategy;
    }

    /**
     * Returns whether deletions should be confirmed by the user.
     *
     * @return bool
     */
    public function confirmDelete()
    {
        if (null === $this->confirm_delete) {
            return (bool) config('cms-models.defaults.confirm_delete', false);
        }

        return (bool) $this->confirm_delete;
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

        $mergeAttributes = [
            'single',
            'meta',
            'verbose_name',
            'translated_name',
            'verbose_name_plural',
            'translated_name_plural',
            'allow_delete',
            'delete_condition',
            'delete_strategy',
            'confirm_delete',
            'list',
            'form',
            'show',
            'export',
            'reference',
            'includes',
        ];

        foreach ($mergeAttributes as $attribute) {
            $this->mergeAttribute($attribute, $with->{$attribute});
        }
    }

}
