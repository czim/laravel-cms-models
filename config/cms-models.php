<?php

use Czim\CmsModels\Support\Enums;
use Czim\CmsModels\Http\Controllers\FormFieldStrategies;
use Czim\CmsModels\Repositories\SortStrategies;
use Czim\CmsModels\View\FilterStrategies;
use Czim\CmsModels\View\ListStrategies;
use Czim\CmsModels\View\ReferenceStrategies;

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Which models to analyze and include model information for.
    |
    */

    'models' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | The base route prefix to prepend for all CMS model module routes.
    |
    */

    'route' => [

        'prefix'      => 'model',
        'name-prefix' => 'models.',

        'meta' => [
            'prefix'      => 'models-meta',
            'name-prefix' => 'models-meta.',
        ],

        // Meta-information endpoint(s) for modelinformation
        'api' => [
            'meta' => [
                'information' => 'models',
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    |
    | Settings that concern the behaviour of the model information repository.
    |
    */

    'repository' => [

        // Whether the mode information should be cached
        'cache' => false,

    ],

    /*
    |--------------------------------------------------------------------------
    | Collector
    |--------------------------------------------------------------------------
    |
    | Model information is collected by a dedicated class defined here.
    |
    */

    'collector' => [

        // The main collector that will be bound to the collector interface
        'class' => Czim\CmsModels\Repositories\Collectors\ModelInformationCollector::class,

        'source' => [

            // The directory that contains the model representations
            'dir' => app_path('Cms/Models'),

            // The base directory that contains the application's models,
            // and the corresponding namespace
            'models-dir'       => app_path('Models'),
            'models-namespace' => 'App\\Models\\',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Analyzer
    |--------------------------------------------------------------------------
    |
    | Settings for the analyzer that builds model information based on
    | Eloquent models.
    |
    */

    'analyzer' => [

        'attributes' => [

        ],

        'scopes' => [

            // Scopes (without the scope prefix) to always ignore
            'ignore' => [
                // translatable
                'translatedIn',
                'notTranslatedIn',
                'translated',
                'listsTranslations',
                'withTranslation',
                'whereTranslation',
                'whereTranslationLike',
                // listify
                'name',
                'listifyScope',
                'inList',
            ],
        ],

        'relations' => [

            // Relations to always ignore
            'ignore' => [
                'translations',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    |
    | The controllers that handle the model routes for web and API requests.
    |
    */

    'controllers' => [
        'models' => [
            'web' => Czim\CmsModels\Http\Controllers\DefaultModelController::class,
            'api' => Czim\CmsModels\Http\Controllers\Api\DefaultModelController::class,
        ],
        'meta' => [
            'web' => Czim\CmsModels\Http\Controllers\ModelMetaController::class,
            'api' => Czim\CmsModels\Http\Controllers\Api\ModelMetaController::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Strategies
    |--------------------------------------------------------------------------
    |
    | Strategies for displaying list and form field content.
    |
    */

    'strategies' => [

        // Strategies for default context/criteria on the repository
        'repository' => [

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\Repositories\\ContextStrategies\\',
            'default-strategy'  => null,

            // Aliases for repository context strategy classes
            'aliases' => [
            ],
        ],

        // Strategies for making model reference strings
        'reference' => [

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\ReferenceStrategies\\',
            'default-strategy'  => ReferenceStrategies\DefaultReference::class,

            // Aliases for reference display strategy classes
            'aliases' => [
                'default' => ReferenceStrategies\DefaultReference::class,
            ],
        ],

        'list' => [

            // The default page size and selectable page size options
            'page-size'         => 25,
            'page-size-options' => [ 25, 50, 100 ],

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\ListStrategies\\',
            'default-strategy'  => ListStrategies\DefaultStrategy::class,

            // The default strategy for sorting columns
            'default-sort-namespace' => 'Czim\\CmsModels\\Repositories\\SortStrategies\\',
            'default-sort-strategy'  => SortStrategies\NullLast::class,

            // Aliases for list display strategy classes
            'aliases' => [
                Enums\ListDisplayStrategy::CHECK              => 'Check',
                Enums\ListDisplayStrategy::CHECK_NULLABLE     => 'CheckNullable',
                Enums\ListDisplayStrategy::DATE               => 'Date',
                Enums\ListDisplayStrategy::TIME               => 'Time',
                Enums\ListDisplayStrategy::DATETIME           => 'DateTime',
                Enums\ListDisplayStrategy::STAPLER_THUMBNAIL  => 'StaplerImage',
                Enums\ListDisplayStrategy::STAPLER_FILENAME   => 'StaplerFile',
                Enums\ListDisplayStrategy::RELATION_COUNT     => 'RelationCount',
                Enums\ListDisplayStrategy::RELATION_REFERENCE => 'RelationReference',
            ],

            // Aliases for sort strategy classes
            'sort-aliases' => [
                'null-last'  => SortStrategies\NullLast::class,
                'translated' => SortStrategies\TranslatedAttribute::class,
            ],

        ],

        'filter' => [

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\FilterStrategies\\',

            // Aliases for filter strategy classes
            'aliases' => [
                'boolean'      => FilterStrategies\DropdownBoolean::class,
                'enum'         => FilterStrategies\DropdownEnum::class,
                'string'       => FilterStrategies\BasicString::class,
                'string-split' => FilterStrategies\BasicSplitString::class,
            ],
        ],

        'form' => [

            // The default namespace to prefix for relative form field display strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\FormFieldStrategies\\',
            'default-strategy'  => FormFieldStrategies\DefaultStrategy::class,

            // The default strategy for storing/retrieving values from models
            'default-store-namespace' => 'Czim\\CmsModels\\Http\\Controllers\\FormFieldStrategies\\',
            'default-store-strategy'  => FormFieldStrategies\DefaultStrategy::class,

            // Aliases for field display strategy classes
            'aliases' => [
                Enums\FormDisplayStrategy::TEXT             => 'DefaultStrategy',
                Enums\FormDisplayStrategy::BOOLEAN_CHECKBOX => 'BooleanCheckboxStrategy',
                Enums\FormDisplayStrategy::BOOLEAN_DROPDOWN => 'BooleanDropdownStrategy',
                Enums\FormDisplayStrategy::TEXTAREA         => 'TextAreaStrategy',
                Enums\FormDisplayStrategy::WYSIWYG          => 'WysiwygStrategy',

                Enums\FormDisplayStrategy::RELATION_SINGLE_DROPDOWN     => 'RelationSingleDropdownStrategy',
                Enums\FormDisplayStrategy::RELATION_SINGLE_AUTOCOMPLETE => 'RelationSingleAutocompleteStrategy',
                Enums\FormDisplayStrategy::RELATION_PLURAL_MULTISELECT  => 'RelationPluralMultiselectStrategy',
                Enums\FormDisplayStrategy::RELATION_PLURAL_AUTOCOMPLETE => 'RelationPluralAutocompleteStrategy',

            ],

            // Aliases for store strategy classes
            'store-aliases' => [
                Enums\FormStoreStrategy::BOOLEAN => 'BooleanStrategy',
            ],

        ],

        'delete' => [

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\Repositories\\DeleteStrategies\\',

            // Aliases for delete strategy classes
            'aliases' => [
            ],

            // The default namespace to prefix for relative strategy class names
            'default-condition-namespace' => 'Czim\\CmsModels\\Repositories\\DeleteConditionStrategies\\',

            // Aliases for delete condition strategy classes
            'condition-aliases' => [
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Default views to use for viewing and editing models.
    |
    */

    'views' => [
        'index'  => 'cms-models::model.index',
        'create' => 'cms-models::model.edit',
        'edit'   => 'cms-models::model.edit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification & Flash Messages
    |--------------------------------------------------------------------------
    |
    | Settings for handling notifications and flash messages for model updates.
    |
    */

    'notifications' => [

        // Whether to flash for specific types of updates
        'flash' => [
            'position' => true,
            'activate' => true,
        ],

    ],

];
