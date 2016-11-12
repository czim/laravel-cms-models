# Model Configuration

Models added to the CMS are analyzed to generate sensible defaults. 
The model's code, related (translation) models and database table will be inspected for this.
 
These default settings may be overridden for any model by using cms model configuration files. 
These are simple array-returning files (the same as Laravel config files).
Anything not defined in model configuration will fall back to the defaults generated by analysis.

You can add models to the CMS by adding them to the `cms-models.models` configuration array, or by creating a model configuration file in the correct namespace (see next section). If a model is represented by either, it will automatically be added to the CMS.

## Configuration Namespace

To override a model, create a `php` file with the exact same filename as the model class file name, and place this in the `app/Cms/Models` directory.

This directory may be configured by setting the `cms-models.collector.source.dir` value to a directory path.
The base directory and namespace where your application models are located can be configured as well (it is `app/Models`, `App\Models\`, by default).

When overriding a model with a nested namespace, such as `App\Models\Library\Book`, place the configuration file in the same relative path to the CMS models dir as the model is to the default model location. 
With default settings, this would be: `app/Cms/Models/Library/Book.php`.


## Example Model Configuration

```php
<?php

return [

    'meta' => [
        'controller' => '\App\Custom\Controller',
    ],

    'reference' => 'title',

    'list' => [

        'columns' => [
            'id',
            'checked',
            'author.name',
            'title',
            'type',
            'created_at',
        ],

        'filters' => [
            'custom' => [
                'label'    => 'Custom Search',
                'target'   => 'author.first_name,author.last_name',
                'strategy' => 'string-split',
            ],
            'title',
            'any' => [
                'label'    => 'Anything',
                'target'   => '*',
                'strategy' => 'string-split',
            ],
        ],

        'scopes' => false,

        'page_size' => [ 20, 40, 60 ],
    ],

];
```

This configuration would define some columns to be present in the listing, some filters to allow quick searches. No tabs would be displayed for model scopes, and users could select some specific page sizes.

## General & Meta Data


### Meta section

The `meta` section stores overriding information for general handling of model-related requests.

To set a different controller for either web or API routes, set the FQN for the controller class in `meta.controller` and/or `meta.controller_api` respectively.

By default, global scopes are disabled when accessing models for the CMS. To prevent this, set `meta.disable_global_scopes` to `false`.

The repository can be set up by defining a strategy class reference (or alias) in `meta.repository_strategy`.
See [Repository Context Strategies](Strategies.md#repository-context-strategies) for more information on the strategies that set the repository's context.


### Model Reference

You can define how models will be referred to within the CMS, by setting the `reference` value (top level).

```
'reference' => [
    'source'   => 'title',
    'strategy' => \Czim\CmsModels\View\ReferenceStrategies\IdAndAttribute::class,
],
```

Keys that may be set:

- **source**: which column(s) or source values to show or use in the reference strategy.
- **strategy**: the reference strategy to apply.
- **search**: the targets (columns, attributes) that will be used for standard means to find matches for the model (in, f.i., a searchable ajax select dropdown).

See [Model Reference Strategies](Strategies.md#model-reference-strategies) for more information on the strategies that render the references.


## List Overrides

The `list` section stores overriding information related to index page listings for models.

### Column Display

The `list.columns` entries determine what columns will be displayed in the model's listing table, as well as how they are ordered.

Entries may be:

- A **string** with an attribute name of the model (ex.:`title`).
    
    This would display the model attribute with a default display strategy based on the attribute analysis.
    
- A **string** with a dot-notation attribute name on a related model (ex.: `author.name`).

    This would display the attribute for a related model's attribute; only recommended for to-one type relationships.
    
- A **string** key, **string** value pair, where the *key* is either of the above types and the *value* is a strategy or strategy alias (ex.: `'checked' => 'boolean-yn'`).

    The value string should indicate a strategy for displaying the column source value. 
    For all other list column properties, the default analysis-determined defaults are used.   

    This is shorthand for the below array notation with only a strategy key defined: `'checked' => ['strategy' => 'boolean-yn']`.
      
- A **string** key, **array** value pair, where the *key* is either of the first two types and the *value* is an associative array with list data key-value pairs.

    The values set in the array will override defaults. For all list column properties not specified, the default analysis-determined defaults are used.

    Ex.:
```
      'title' => [
          'source'         => 'title',
          'label'          => 'Main Title',
          'strategy'       => 'string',
          'sort_strategy'  => 'null-first',
          'sort_direction' => 'desc',
      ],
```

Note that if the `columns` section is omitted from the configuration, this can easily result in (too) many columns being rendered for hefty models.

See [List Display Strategies](Strategies.md#list-display-strategies) for information about strategies that can be used (or how to create new ones).


### Default Sorting

The default sort key may be set in `list.default_sort`. This may indicate any model attribute or custom column key defined.

The default sorting direction is determined by the relevant `sort_direction` setting (`'asc'` or `'desc'`) of the list column referenced.


### Filter

In `list.filters`, the fields may be defined by which a listing can be filtered. If any field is defined here, any default fields not included will be omitted.

This section works very much like the `list.columns`.

If the only filter needed is a text input for any textual column, loosely matching split terms, use this:

```
'filters' => [
    'any' => [
        'label'    => 'Anything',
        'target'   => '*',
        'strategy' => 'string-split',
    ]   
]
```

This will take a an input like 'blue shoes' and search for it as `like '%blue%' OR like '%shoes%'`, in any char or text based field, included in translations for the model.

Accepted values:

- A **string** with an attribute name of the model (ex.:`title`).
- A **string** with a dot-notation attribute name on a related model (ex.: `author.name`).
- A **string** key, **string** value pair, where the *key* is either of the above types and the *value* is a strategy or strategy alias (ex.: `'title' => 'string-split'`).      
- A **string** key, **array** value pair, where the *key* is either of the first two types and the *value* is an associative array with list data key-value pairs.

    The values set in the array will override defaults. For all filters values not specified, the analysis-determined defaults are used.
    
See [Filter Strategies](Strategies.md#filter-strategies) for information about strategies that can be used (or how to create new ones), and the key-value pairs in the filter data.

To disable all filters entirely, set `list.disable_filters` to `true`.

## Activatable

By default, models detected to have an `active` boolean column will allow this to be toggled in the list view.

To disable this, set `list.activatable` to `false`.

Alternatively, `list.active_column` may be set to the attribute name of the column that should be toggled through the controls. 
This should be a boolean column.


## Orderable

By default, models detected to have the `Listify` trait (whether mine or lookitsatravis's version), will be orderable in the listing.

To disable this, set `list.orderable` to `false`.
This will hide the controls and drag-and-drop interface for changing list positions.

To change the listify column or strategy, set `list.order_column` and `list.order_strategy`, respectively. This should not generally be necessary; if required, it is probably better to change the model analysis instead.

## Scopes

Scopes are automatically read on model analysis and added as tabs above the listing.

Scope functionality (and the presence of tabs) may be disabled entirely by setting `list.disable_scopes` to `true`.

It is also possible to set an array custom scopes with `list.scopes`. 
If this is set, the default scopes will be overruled.
 
 Example:
 
 ```
 'scopes' => [
     'some_scope_key' => [
         `method`   => null,
         'label'    => 'Scope Label',
         `label_translated' => 'cms::your.trans.key',
         'strategy' => '\Your\Scope\Strategy',
     ]   
 ]
 ```

Accepted values for each array item:
 
- A **string** with the name of an existing scope on the model.
- A **string** key, **array** value pair, where the *key* is either of the first two types and the *value* is an associative array with list data key-value pairs.

    The values set in the array will override defaults. For all scope values not specified, the analysis-determined defaults are used. See the example above.

Note that setting `label_translated` overrides the `label` value.

When creating a custom scope, note that the `method` and `strategy` settings are mutually exclusive.
A scope either has an Eloquent scope method name set, or a custom scope strategy class.

See [Scope Strategies](Strategies.md#scope-strategies) for information about making strategy classes for custom scopes.


## Page Size

The default page size and custom page sizes that users can select for the listing are set in the `cms-models.strategies.list.page-size` and `cms-models.strategies.list.page-size-options` config keys.

These settings may be overruled for any model by setting its `list.page_size` in the model config.
 
The value may be:

- An **integer** value to set a fixed page size
- An **array** of integers, the first of which is the default page size. 
    The others are added as selectable options. 
