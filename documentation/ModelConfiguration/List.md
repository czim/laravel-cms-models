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


### Column Display Values

The key values pairs that may be set for a column display are as follows.

- `source` (string)  
    An identifier for a source relative to the model record displayed.  
    This may be the name of a model attribute (translated or not), or a dot-notation reference to an attribute on a related model.
    If this is not set, the list column key is used.
    
- `strategy` (string)  
    An FQN, class name or alias for a list display strategy.
    
- `label` (string)  
    A label text to display in the table header.  
    If this is not set, the list column key is used (prettified a bit).

- `label_translated` (string)  
    A translation key to use for a translated table header.  
    If this is set, it overrides `label`.

- `sortable` (boolean)  
    Whether the column allows sorting.
    
- `sort_strategy` (string)  
    The strategy for sorting the column.  
    If none is set, it will default to an alphanumeric, null-last sort.

- `sort_direction` (string: 'asc' or 'desc')  
    The direction to sort in initially.  
    Defaults to `'asc'` for most columns (`'desc'` for model timestamps and numeric primary keys). 

- `options` (array)  
    A list of key-value pairs that may set options to be used by the relevant strategies.

- `disable_default_action` (boolean)  
    If this is set to `true`, any default row-click action will be disabled for this column. This is useful for preventing problems with columns that offer clickable events that may clash with the 'normal' click action.

- `hide` (boolean)  
    If `true`, hides the column from the listing entirely.
    Mainly useful for hiding columns without having to remove the settings entirely.


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
        'strategy'         => 'string-split',
        'label_translated' => 'models.filter.anything-label',
        'target'           => '*',
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


## Default Actions

Purely for convenience, it is possible to set a default action for clicking on a model record row in the listing table in `list.default_action`.

This must be an array with one or more arrays with the following keys:

- `type` (string)  
    A special type for a built-in route for the model.
    This may be `'edit'` or `'show'` for a quick link to edit/show the model record.  
    Required if `route` is not set.
    
- `route` (string)  
    The route name for a (CMS) route to use as the action.  
    Required if `type` is not set.

- `permissions` (string|string[])  
    One or more permissions that must all be granted in order to use the action. If the user does not have the permission(s), this action will not be offered.

- `variables` (string[])  
    A list of variable names to use as parameters for the `route`.  
    Not to be used in combination with `type`.  
    The variables must be available in the index view (f.i.: `'model'` to use `$model`, and `'modelKey'` to use the primary key of the model record for the row).

The first action that is permitted will be used. 
This way, a list of potential actions may be set up, offering fall-backs when fewer permissions are granted.

Example:

```php
<?php
    'list' => [
        
        'default_action' => [
            // Action for users with 'edit' permission
            [
                'type'        => 'edit',
                'permissions' => 'models.app-models-post.edit',
            ],
            // Action for users with 'show' permission
            [
                'type'        => 'show',
                'permissions' => 'models.app-models-post.show',
            ],
            // Fall-back action when no permissions given
            [
                'route'       => 'some.fallback.route',
                'variables'   => [ 'modelKey' ],
            ]
        ],
        
        // ...
    ],
```

## Custom Before or After Views

To futher customize the listing page, it is possible to indicate a `list.before` and/or `list.after` view reference: a pointer to a view path and (optionally) a list of variables that should be passed into it.

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
