# List Filter Strategy: Dropdown Boolean

For filtering on boolean attributes with a select dropdown.

Offers an empty option for not filtering, a `false` and `true` option.


## Options

- `true_label` (string)  
    The display label to use for the 'true' option in the dropdown.  
    If this, nor the translated label is set, a default translation is used ('common.boolean.true').
    
- `true_label_translated` (string)  
    The translation key to use for the 'true' option in the dropdown.  
    Overrules the `true_label`, if set.

- `false_label` (string)  
    The display label to use for the 'false' option in the dropdown.  
    If this, nor the translated label is set, a default translation is used ('common.boolean.false').

- `false_label_translated` (string)  
    The translation key to use for the 'false' option in the dropdown.  
    Overrules the `false_label`, if set.


Example:

```php
    'options' => [
        'true_label_translated' => 'common.boolean.enabled',
        'false_label'           => 'DISABLED',
    ]
```
