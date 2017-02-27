# Model Configuration: Form layout & Fields

## Fields

Form fields may be included or overridden in the `form.fields` array.
 

### Including fields

The CMS will generate sensible default field data in most cases, and is set to display most fields automatically.

To use the defaults for a specific set of fields to include at the exclusion of others, add them as string values in the fields array:
 
 ```php
 <?php
 return [
    // ...
    
    'form' => [
        'fields' => [
            'title',
            'body',
            'genre',
            'tags',
        ]
    ],
    
    // ...
];
```

If fields are defined, only the ones included in the configuration will be displayed.
 

### Overriding fields

Keys that may be set:

- `store_strategy` (string)  
    The strategy that is used to retrieve and store the date edited using this field.

- `display_strategy` (string)  
    The strategy that is used to render this field.

- `source` (string)  
    Indicates the source to be used for the data edited using this field. Default: field entry's key.

- `label` (string)  
    Explicitly label to show next to the form field.
        
- `label_translated` (string)  
    The translation key for a CMS translation to use as the field label.
    If set, this overrides the `label`.

- `required` (bool)  
    Whether the field may not be left empty.
    If not explicitly set, model analysis sets this to true for attributes that are not `nullable`.

- `translated` (bool)  
    Whether the field corresponds to an attribute on a translation of the record.  
    It is not recommended to set this manually. This defaults to a model analysis result that checks for a Translatable setup.

- `create` (bool)  
    Set to false to hide the field on the form to create a new record.
     
- `update` (bool)  
    Set to false to hide the field on forms to edit an existing record.

- `admin_only` (bool)  
    Set to true to make the field only usable by super admins.
    This overrides any `permissions`.
    
- `permissions` (string or array of strings)  
    The permission(s) required to be able to use this field.
    If more than one, users must have all permissions in order to use the field.  
    Note that any *custom* permissions added here will not automatically be known by the CMS (or ACL module). For that, they must be added to the CMS configuration. For more information, check the documentation for your chosen [ACL module](https://github.com/czim/laravel-cms-acl-module). 

- `options` (array, associative)  
    Options pertaining to either or both of the strategies used.

- `before` (array)  
    A view reference for including a custom view partial before the form field.  
    Example:  
```php
<?php
  'before' => [
      'view'      => 'partials.some.path.index',
      'variables' => [ 'field', 'record' ]
  ]
```
  
- `after` (array)
    A view reference for including a custom view partial after the form field.


Any field included in the configuration will be displayed for both the create and update forms, unless explicitly defined otherwise.
 
To hide a field from a form without removing it from the fields array, set its `create` and/or `update` key to false.


### Field options

Each field definition may have an `options` associative array value.
What keys may be set is determined by the strategies (`store_strategy` and/or `display_strategy`) for the field.
More information on this may be found in the documentation of the [form strategies](../FormStrategyReference.md).


### Custom fields

Any field may be added to the form.
There is no need for the key to exist as either an attribute or relation on the model. 
Custom strategies can and should usually be created to handle such fields.
 
For custom fields, there are no defaults the CMS can fall back on, so the form field must be defined, including at least `source` and `store_strategy` value.


### Example Fields

```php
<?php
return [
    // ...
    
    'form' => [
        'fields' => [
            
            'title' => [
                'source'           => 'title_text',
                'display_strategy' => 'wysiwyg',
            ],
            
            'body' => [
                'create'      => false,
                'permissions' => 'must.have.this.permission',
            ],
        ]
    ]
    
    // ...
];
```


## Layout

Form fields defined may be arranged in a custom layout in the `form.layout` array.

If no layout is set, all fields will be shown on a single page, in the order in which they are defined in the `form.fields` array.


### Tab Panes

Fields may be split over different tab panes. 

It should have a `label` or a `label_translated`, displayed in the tab lip. 
`label_translated` should be an available translation key.

If tab panes are used, all top level layout entries must be tabs.
Otherwise, any non-tab top level entries will be ignored.

Tabs contents are part of the same form and will be submitted simultaneously.

Tab panes may also have arrays set for `before` and `after` keys, to set custom view partials.
This works similarly to the before/after keys [for the form itself](#custom-before-or-after-views).

Example:

```php
<?php
    'tab-1' => [
        'type'  => 'tab',
        'label' => 'Main Fields',
        'children' => [
            // ...
        ],
        'before' => [
            'view' => 'some.custom.view'            
        ]
    ],
```

### Fieldsets

Fields may be grouped in fieldsets.

The type for this layout node is `fieldset`.
It may optionally have a `label` or a `label_translated`, displayed as the fieldset 'legend'.


### Field Groups

Fields may be grouped on a single form row by nesting them under a field group.

The type for this layout node is `group`.

Field groups do not support nesting; only use field keys or field labels (see below) as children of this layout node type.

It should have a `label` or a `label_translated`, displayed as the main label for the form row.

Additionally, the following properties may be set:

- `columns` (array of integers)  
    The grid column layout of the field group row.
    The grid size used is 12, the field group label takes up 2;
    The remaining grid size of 10 may be distributed over the group children. See the author group in the example layout below.  
    By default, this sets an even spread: labels will take up 2, fields evenly divide up the remaining grid space.  

- `label_for` (string)  
    The field key for which this is a label. This will set a `for` attribute on the label tag, pointing to the correct input.

### Field Labels

This serves as an in-line label for further fields within the field group. This should only be used in field groups.

It should have a `label` or a `label_translated`.

Additionally, the following property may be set:

- `label_for` (string)  
    The field key for which this is a label. This will set a `for` attribute on the label tag, pointing to the correct input.


### Example layout

```php
<?php
return [
    // ...
    
    'form' => [
        
        'layout' => [
            'tab-1' => [
                'type'  => 'tab',
                'label' => 'Main Fields',
                'children' => [
                    'title',
                    'subtitle',
                    'genre',
                    [
                        'type'  => 'group',
                        'label' => 'Author',
                        'label_for' => 'author_first_name',
                        'columns' => [ 3, 2, 5 ],
                        'children' => [
                            'author_first_name',
                            [
                                'type' => 'label',
                                'label' => 'Last Name',
                                'label_for' => 'author_last_name',
                            ],
                            'author_last_name',
                        ]
                    ]
                ]
            ],
            'tab-2' => [
                'type'  => 'tab',
                'label' => 'Extra Tab Pane',
                'children' => [
                    'category',
                    'type',
                    'fieldset-content' => [
                        'type'  => 'fieldset',
                        'label' => 'Content',
                        'children' => [
                            'introduction',
                            'body',
                            'footer',
                            'summary',
                        ]
                    ]
                ]
            ]
        ],
        
        'fields' => [
            // ...            
        ]
    ],
    
    // ...
];
```

## Validation Rules

Validation rules are defined in the `form.validation`  section of the model configuration.
 
They defined per [form field](#fields) key.
The syntax should follow standard Laravel validation rule syntax: a string with `|` separated rules, or an array of rule strings.


### Create & Update Rules

By default (in CMS-generated validation rules configuration), create and update form validation rules are the same.

When setting rules manually that should similarly affect both forms, set them in the `create` section. 
As long as the `update` section is left empty, create rules will be used in any case.

Example:
```php
<?php
return [
    // ...
    
    'form' => [
        // ...
        
        'validation' => [
            
            'create' => [
                'name'        => 'required|string',
                'description' => [ 'string', 'max:512' ],
            ]
        ]
    ]
];
```

It is possible to make different rules for the create and update forms, by defining rules for each section separately.\

In case only the `update` section rules are set, create rules are left default.

Example:

```php
<?php
return [
    // ...
    
    'form' => [
        // ...
        
        'validation' => [
        
            // Only require the 'name' field to be filled when creating.
            'create' => [
                'name' => 'required|string',
            ],
            
            'update' => [
                'name' => 'string',
            ]
        ]
    ]
];
```

### Overriding vs. Enriching Rules

By default, any configured rules are will replace all default rules *for that form field*, but leave all *other* form field validation rules unaffected.

It is possible to change this behaviour using the `form.validation.create_replace` and `form.validation.update_replace` keys. 
Set them to `true` to let the `create` and `update` sections, respectively, completed replace any default CMS-defined rules.
If this is used, any key not present in the relevant rule section will *not* have validation rules at all.

Example:
```php
<?php
return [
    // ...
    
    'form' => [
        // ...
        
        'validation' => [
        
            // Put the update rule section in 'replace' mode. 
            'update_replace' => true,
            
            // Now the *only* validation rule for the update form 
            // will be for the 'name' form field.
            'update' => [
                'name' => 'required|string',
            ]
            
            // The create rules are unaffected by this.
        ]
    ]
];
```

Regardless of whether rules are replaced or enriched, any rule configured with a value of `false` or `null` will be disabled.
No default rules for that field key (or specific rule key) will be included in the final ruleset.


## Custom Before or After Views

To futher customize the form page, it is possible to indicate `before` and/or `after`, `before_form` and/or `after_form` view references: pointers to a view path and (optionally) a list of variables that should be passed into them.

`before` and `after` views will be displayed outside of the `<form>` tag, `before_form` and `after_form` inside of it. Additionally `after_form` will be displayed above the form submit button row.


Example:

```php
<?php
return [
    // ...
    
    'form' => [
        
        'before' => [
            'view'      => 'partials.some.path.index',
            'variables' => [ 'record', 'model' ]
        ],
        
        'after_form' => [
            'view' => 'partials.some.other.path.index'
        ],
    
        // ...
    ],
    
    // ...
];
```
