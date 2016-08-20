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

            // The directory that contains the model representation
            'dir' => app_path('Cms/Models'),

            // The base directory that contains the application's models
            'models-dir' => app_path('Models'),
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
                'translatedIn',
                'notTranslatedIn',
                'translated',
                'listsTranslations',
                'withTranslation',
                'whereTranslation',
                'whereTranslationLike',
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

        'list' => [

            // The default page size and selectable page size options
            'page-size'         => 25,
            'page-size-options' => [ 25, 50, 100 ],

            // The default namespace to prefix for relative strategy class names
            'default-namespace' => 'Czim\\CmsModels\\View\\ListStrategies\\',

            // The default strategy for sorting columns
            'default-sort-namespace' => 'Czim\\CmsModels\\Repositories\\SortStrategies\\',
            'default-sort-strategy'  => Czim\CmsModels\Repositories\SortStrategies\NullLast::class,
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
        'create' => 'cms-models::model.create',
        'edit'   => 'cms-models::model.edit',
    ],

];
