<?php
namespace Czim\CmsModels\ModelConfig;

class Model
{

    /**
     * @var array
     */
    protected $main = [];

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var array
     */
    protected $reference = [];

    /**
     * @var array
     */
    protected $includes = [];

    /**
     * @var Field[]
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $export = [];

    /**
     * @var ModelList
     */
    protected $list;

    /**
     * @var ModelForm
     */
    protected $form;


    public function __construct()
    {
        $this->list = new ModelList;
        $this->form = new ModelForm;
    }


    public static function make(): Model
    {
        return new static;
    }


    public function toArray(): array
    {
        return $this->buildModelInformationArray();
    }

    /**
     * @param array $fields
     * @return Model|$this
     */
    public function fields(array $fields): Model
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Provide a callable to configure the list.
     *
     * @param callable $configureList
     * @return Model|$this
     * @see ModelList
     */
    public function list(callable $configureList): Model
    {
        $configureList($this->list);

        return $this;
    }

    /**
     * Provide a callable to configure the editing form.
     *
     * @param callable $configureForm
     * @return Model|$this
     * @see ModelList
     */
    public function form(callable $configureForm): Model
    {
        $configureForm($this->form);

        return $this;
    }

    // ------------------------------------------------------------------------------
    //      Main
    // ------------------------------------------------------------------------------

    /**
     * FQN of the model to use for saving the Model's data
     *
     * @param string $class FQN
     * @return Model|$this
     */
    public function model(string $class): Model
    {
        $this->main['model'] = $class;

        return $this;
    }

    /**
     * FQN Even if model_class isn't this should always be the original Model described
     *
     * @param string $class FQN
     * @return Model|$this
     */
    public function originalModel(string $class): Model
    {
        $this->main['original_model'] = $class;

        return $this;
    }

    /**
     * Display name for the model (or translation key)
     *
     * @param string $name literal name
     * @return Model|$this
     */
    public function literalName(string $name): Model
    {
        $this->main['verbose_name'] = $name;

        return $this;
    }

    /**
     * Display name for the model (or translation key)
     *
     * @param string $name translation key
     * @return Model|$this
     */
    public function translatedName(string $name): Model
    {
        $this->main['translated_name'] = $name;

        return $this;
    }

    /**
     * Plural display name for the model
     *
     * @param string $name literal name
     * @return Model|$this
     */
    public function pluralLiteralName(string $name): Model
    {
        $this->main['verbose_name_plural'] = $name;

        return $this;
    }

    /**
     * Plural display name for the model as translation key
     *
     * @param string $name translation key
     * @return Model|$this
     */
    public function pluralTranslatedName(string $name): Model
    {
        $this->main['translated_name_plural'] = $name;

        return $this;
    }

    /**
     * The model will always only have exactly one record
     *
     * @return Model|$this
     */
    public function isSingle(): Model
    {
        $this->main['single'] = true;

        return $this;
    }

    /**
     * The model uses Translatable (or a compatible translation strategy)
     *
     * Is normally autodetected.
     *
     * @return Model|$this
     */
    public function translated(): Model
    {
        $this->main['translated'] = true;

        return $this;
    }

    /**
     * The strategy by which the model is translated. For now, this should always be 'translatable'.
     *
     * @param string $strategy
     * @return Model|$this
     */
    public function translationStrategy(string $strategy): Model
    {
        $this->main['translation_strategy'] = $strategy ?: 'translatable';

        return $this;
    }

    /**
     * Disallow deletion entirely (even disregarding permissions set)
     *
     * @return Model
     */
    public function disallowDelete(): Model
    {
        $this->main['allow_delete'] = false;

        return $this;
    }

    /**
     * Whether to require confirmation of deletion (default: true)
     *
     * @param bool $confirm
     * @return Model
     */
    public function confirmDelete(bool $confirm = true): Model
    {
        $this->main['confirm_delete'] = $confirm;

        return $this;
    }

    /**
     * A strategy for allowing deletion (such as not being linked to from other models, etc)
     *
     * Multiple delete strategies may be set (they stack).
     *
     * @param string $strategy      alias or FQN
     * @param array  $parameters    scalar values in order
     * @return Model
     */
    public function deleteConditionStrategy(string $strategy, array $parameters = []): Model
    {
        if ( ! array_has($this->main, 'delete_condition')) {
            $this->main['delete_condition'] = '';
        }

        $this->main['delete_condition'] .= ($this->main['delete_condition'] ? '|' : '') . $strategy;

        if (count($parameters)) {

            $serializedParameters = implode(
                ',',
                array_map(
                    function ($value) {
                        return (string) $value;
                    },
                    $parameters
                )
            );

            $this->main['delete_condition'] .= ':' . $serializedParameters;
        }

        return $this;
    }

    /**
     * Plural display name for the model as translation key
     *
     * @param string $name alias or FQN
     * @return Model|$this
     */
    public function deleteStrategy(string $name): Model
    {
        $this->main['delete_strategy'] = $name;

        return $this;
    }

    /**
     * Indicate that the model's primary key does not auto-increment
     *
     * @return Model|$this
     */
    public function primaryKeyDoesNotIncrement(): Model
    {
        $this->main['incrementing'] = false;

        return $this;
    }

    /**
     * Model has timestamp columns
     *
     * @param string $createdAttribute  defaults to 'created_at'
     * @param string $updatedAttribute  defaults to 'updated_at'
     * @return Model|$this
     */
    public function hasTimestamps(string $createdAttribute = null, string $updatedAttribute = null): Model
    {
        $this->main['timestamps']        = true;
        $this->main['timestamp_created'] = $createdAttribute ?: 'created_at';
        $this->main['timestamp_updated'] = $updatedAttribute ?: 'updated_at';

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Meta
    // ------------------------------------------------------------------------------

    /**
     * @param string $class FQN
     * @return Model|$this
     */
    public function controller(string $class): Model
    {
        $this->meta['controller'] = $class;

        return $this;
    }

    /**
     * @param string $method
     * @return Model|$this
     */
    public function controllerDefaultMethod(string $method): Model
    {
        $this->meta['default_controller_method'] = $method;

        return $this;
    }

    /**
     * @param string $class FQN
     * @return Model|$this
     */
    public function apiController(string $class): Model
    {
        $this->meta['controller_api'] = $class;

        return $this;
    }

    /**
     * @param string $strategy
     * @param array  $parameters
     * @return Model|$this
     */
    public function repositoryStrategy(string $strategy, array $parameters = []): Model
    {
        $this->meta['repository_strategy']            = $strategy;
        $this->meta['repository_strategy_parameters'] = $parameters;

        return $this;
    }

    /**
     * @return Model|$this
     */
    public function disableAllGlobalScopes(): Model
    {
        $this->meta['disable_global_scopes'] = true;

        return $this;
    }

    /**
     * @param array $scopeNames
     * @return Model|$this
     */
    public function disableGlobalScopes(array $scopeNames): Model
    {
        $this->meta['disable_global_scopes'] = $scopeNames;

        return $this;
    }

    /**
     * @param string $class FQN
     * @return Model|$this
     */
    public function createFormRequest(string $class): Model
    {
        array_set($this->meta, 'form_requests.create', $class);

        return $this;
    }

    /**
     * @param string $class FQN
     * @return Model|$this
     */
    public function updateFormRequest(string $class): Model
    {
        array_set($this->meta, 'form_requests.update', $class);

        return $this;
    }

    /**
     * @param string $name
     * @return Model|$this
     */
    public function indexView(string $name): Model
    {
        array_set($this->meta, 'views.index', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return Model|$this
     */
    public function showView(string $name): Model
    {
        array_set($this->meta, 'views.show', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return Model|$this
     */
    public function createView(string $name): Model
    {
        array_set($this->meta, 'views.create', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return Model|$this
     */
    public function editView(string $name): Model
    {
        array_set($this->meta, 'views.edit', $name);

        return $this;
    }

    /**
     * @param string $class FQN
     * @return Model|$this
     */
    public function apiTransformer(string $class): Model
    {
        $this->meta['transformer'] = $class;

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Reference
    // ------------------------------------------------------------------------------

    /**
     * The strategy for displaying the reference
     *
     * @param string $strategy alias or strategy class
     * @return Model|$this
     */
    public function referenceStrategy(string $strategy): Model
    {
        $this->reference['strategy'] = $strategy;

        return $this;
    }

    /**
     * The source attribute list to use in the strategy
     *
     * @param string|string[] $attribute attribute name
     * @return Model|$this
     */
    public function referenceSourceAttribute(string $attribute): Model
    {
        $this->reference['source'] = $attribute;

        return $this;
    }

    /**
     * The source attribute list to use in the strategy
     *
     * @param string[] $attributes attribute names
     * @return Model|$this
     */
    public function referenceSourceAttributes(array $attributes): Model
    {
        $this->reference['source'] = $attributes;

        return $this;
    }

    /**
     * The attributes to search for matches on (f.i. selecting records to link to)
     *
     * @param string[] $attributes attribute names
     * @return Model|$this
     */
    public function referenceSearchAttributes(array $attributes): Model
    {
        $this->reference['search'] = $attributes;

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Includes
    // ------------------------------------------------------------------------------

    /**
     * List of available includes to allow
     *
     * Includes for model eager loading, instance of ModelIncludesData
     *
     * Each item should be either a relation name string, or relation name key string => callable strategy
     *
     * @param string[] $relations include strings
     * @return Model|$this
     */
    public function availableIncludes(array $relations): Model
    {
        $this->includes['available'] = $relations;

        return $this;
    }

    /**
     * List of default includes to use for loading a model
     *
     * Includes for model eager loading, instance of ModelIncludesData
     *
     * Each item should be either a relation name string, or relation name key string => callable strategy
     *
     * @param string[] $relations include strings
     * @return Model|$this
     */
    public function defaultIncludes(array $relations): Model
    {
        $this->includes['default'] = $relations;

        return $this;
    }



    // ------------------------------------------------------------------------------
    //      Export
    // ------------------------------------------------------------------------------

    /**
     * Export is allowed
     *
     * @return Model|$this
     */
    public function exportEnabled(): Model
    {
        $this->export['enable'] = true;

        return $this;
    }


    protected function buildModelInformationArray(): array
    {
        $array = $this->main;

        $array['meta']      = $this->meta;
        $array['reference'] = $this->reference;
        $array['includes']  = $this->includes;
        $array['export']    = $this->export;

        $this->applyFieldsDataToArray($array);

        return $array;
    }

    protected function applyFieldsDataToArray(array &$array): void
    {
        // todo
        //      analyze fields data and splice into the correct subfields
        //      accordingly
    }

}
