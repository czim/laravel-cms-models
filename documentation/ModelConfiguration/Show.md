# Model Configuration: Show Page Configuration

The show model page may display a list of 'fields' with values for the model.  
These may re-use ListColumnDisplay strategies (despite the name) and will be displayed as part of a list of 'static' label/field combinations.


## Fields

Show fields may be included or overridden in the `show.fields` array.
 

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

- `strategy` (string)  
    The strategy that is used to render this field.

- `source` (string)  
    Indicates the source to be used for the data shown using this field. Default: field entry's key.

- `label` (string)  
    Explicitly label to show next to the field value.
        
- `label_translated` (string)  
    The translation key for a CMS translation to use as the field label.
    If set, this overrides the `label`.

- `translated` (bool)  
    Whether the field corresponds to an attribute on a translation of the record.  
    It is not recommended to set this manually. This defaults to a model analysis result that checks for a Translatable setup.

- `admin_only` (bool)  
    Set to true to make the field only visible to super admins.
    This overrides any `permissions`.
    
- `permissions` (string or array of strings)  
    The permission(s) required to be able to see this field.
    If more than one, users must have all permissions in order to see the field.  
    Note that any *custom* permissions added here will not automatically be known by the CMS (or ACL module). For that, they must be added to the CMS configuration. For more information, check the documentation for your chosen [ACL module](https://github.com/czim/laravel-cms-acl-module). 

- `options` (array, associative)  
    Options pertaining to the strategy used.


### Field options

Each field definition may have an `options` associative array value.
What keys may be set is determined by the strategies (`strategy`) for the field.
More information on this may be found in the documentation of the [list display strategies](../ListStrategyReference.md).


### Custom fields

Any field may be added to the show page.
There is no need for the key to exist as either an attribute or relation on the model. 
Custom strategies can and should usually be created to handle such fields.
 
For custom fields, there are no defaults the CMS can fall back on, so the show field must be defined, including at least `source` and `strategy` value.


## Custom Before or After Views

To futher customize the show model page, it is possible to indicate `before` and/or `after` view references: pointers to a view path and (optionally) a list of variables that should be passed into them.

Example:

```php
<?php
return [
    // ...
    
    'show' => [
        
        'before' => [
            'view'      => 'partials.some.path.index',
            'variables' => [ 'record', 'model' ]
        ],
        
        // ...
    ],
    
    // ...
];
```
