# Model Configuration: List

The `list` section stores overriding information related to index page listings for models.


## Column Display

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

See [List Display Strategies](../Strategies.md#list-display-strategies) for information about strategies that can be used (or how to create new ones).


## Default Sorting

The default sort key may be set in `list.default_sort`. This may indicate any model attribute or custom column key defined.

The default sorting direction is determined by the relevant `sort_direction` setting (`'asc'` or `'desc'`) of the list column referenced.


## Filter

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
    
See [Filter Strategies](../Strategies.md#filter-strategies) for information about strategies that can be used (or how to create new ones), and the key-value pairs in the filter data.

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

See [Scope Strategies](../Strategies.md#scope-strategies) for information about making strategy classes for custom scopes.


## Page Size

The default page size and custom page sizes that users can select for the listing are set in the `cms-models.strategies.list.page-size` and `cms-models.strategies.list.page-size-options` config keys.

These settings may be overruled for any model by setting its `list.page_size` in the model config.
 
The value may be:

- An **integer** value to set a fixed page size
- An **array** of integers, the first of which is the default page size. 
    The others are added as selectable options. 


## Custom Before or After Views

To futher customize the listing page, it is possible to indicate a `before` and/or `after` view reference: a pointer to a view path and (optionally) a list of variables that should be passed into it.

Example:

```php
<?php
    
    'list' => [
        
        'before' => [
            'view'      => 'partials.some.path.index',
            'variables' => [ 'records', 'model' ]
        ],
    
        // ...
```
