<?php

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
        'class' => \Czim\CmsModels\Repositories\Collectors\ModelInformationCollector::class,

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
        'web' => Czim\CmsModels\Http\Controllers\DefaultModelController::class,
        'api' => Czim\CmsModels\Http\Controllers\Api\DefaultModelController::class,
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
            'default-strategy'  => null,

            // Aliases for reference display strategy classes
            'aliases' => [
            ],
        ],

        'list' => [

            // The default page size and selectable page size options
            'page-size'         => 25,
            'page-size-options' => [ 25, 50, 100 ],

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\ListStrategies\\',
            'default-strategy'  => Czim\CmsModels\View\ListStrategies\DefaultStrategy::class,

            // The default strategy for sorting columns
            'default-sort-namespace' => 'Czim\\CmsModels\\Repositories\\SortStrategies\\',
            'default-sort-strategy'  => Czim\CmsModels\Repositories\SortStrategies\NullLast::class,

            // Aliases for list display strategy classes
            'aliases' => [
                'check'     => Czim\CmsModels\View\ListStrategies\Check::class,
                'count'     => Czim\CmsModels\View\ListStrategies\RelationCount::class,
                'reference' => Czim\CmsModels\View\ListStrategies\RelationReference::class,
            ],

            // Aliases for sort strategy classes
            'sort-aliases' => [
                'null-last'  => Czim\CmsModels\Repositories\SortStrategies\NullLast::class,
                'translated' => Czim\CmsModels\Repositories\SortStrategies\TranslatedAttribute::class,
            ],

        ],

        'filter' => [

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\FilterStrategies\\',

            // Aliases for filter strategy classes
            'aliases' => [
                'boolean'      => Czim\CmsModels\View\FilterStrategies\DropdownBoolean::class,
                'enum'         => Czim\CmsModels\View\FilterStrategies\DropdownEnum::class,
                'string'       => Czim\CmsModels\View\FilterStrategies\BasicString::class,
                'string-split' => Czim\CmsModels\View\FilterStrategies\BasicSplitString::class,
            ],
        ],

        'form' => [

            // The default namespace to prefix for relative form field display strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\FormFieldStrategies\\',
            'default-strategy'  => Czim\CmsModels\View\FormFieldStrategies\DefaultStrategy::class,

            // The default strategy for storing/retrieving values from models
            'default-store-namespace' => 'Czim\\CmsModels\\Http\\Controllers\\FormFieldStrategies\\',
            'default-store-strategy'  => Czim\CmsModels\Http\Controllers\FormFieldStrategies\DefaultStrategy::class,

            // Aliases for field display strategy classes
            'aliases' => [
            ],

            // Aliases for store strategy classes
            'store-aliases' => [
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

];
