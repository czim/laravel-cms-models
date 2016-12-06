# Model Configuration: Form layout & Fields

## Fields

Form fields may be included or overridden in the `form.fields` array.
 

### Including fields

The CMS will generate sensible default field data in most cases, and is set to display most fields automatically.

To use the defaults for a specific set of fields to include at the exclusion of others, add them as string values in the fields array:
 
 ```php
    'fields' => [
        'title',
        'body',
        'genre',
        'tags',
    ]
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

- `options` (array, associative)  
    Options pertaining to either or both of the strategies used.


Any field included in the configuration will be displayed for both the create and update forms, unless explicitly defined otherwise.
 
To hide a field from a form without removing it from the fields array, set its `create` and/or `update` key to false.


### Field options

Each field definition may have an `options` associative array value.
What keys may be set is determined by the strategies (`store_strategy` and/or `display_strategy`) for the field.
More information on this may be found in the documentation of the [form strategies](../FormStrategyReference.md).


### Custom fields

Any field may be added to the form.
There is no need for the key to exist as either an attribute or relation on the form. 
Custom strategies can and should usually be created to handle such fields.
 
For custom fields, there are no defaults the CMS can fall back on, so the form field must be defined, including at least `source` and `store_strategy` value.


### Example Fields

```php
    'fields' => [
        
        'title' => [
        ],
        
        'body' => [
            'create' => false,
        ],
    ]
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


### Fieldsets

Fields may be grouped in fieldsets.

The type for this layout node is `fieldset`.
It may optionally have a `label` or a `label_translated`, displayed as the fieldset 'legend'.


### Field Groups

Fields may be grouped on a single form row by nesting them under a field group.

The type for this layout node is `group`.
It should have a `label` or a `label_translated`, displayed as the main label for the form row. 


### Example layout

```php
    'layout' => [
        'tab-1' => [
            'type'  => 'tab',
            'label' => 'Main Fields',
            'children' => [
                'title'
                'subtitle',
                'genre',
                [
                    'type'  => 'group',
                    'label' => 'Author',
                    'children' => [
                        'author_first_name',
                        'author_last_name',
                    ]
                ]
            ]
        ],
        'tab-2' => [
            'type'  => 'tab',
            'label' => 'Extra Tab Pane',
            'children' => [
                'category'
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
    ]
```
