# List Filter Strategy: Dropdown Enum

For filtering by selecting a single value from a select dropdown.
This is intended for use with `enum` attributes, but may be used for any custom filter.

Offers an empty option for not filtering, and a customizable list of options.


## Options

The options for this strategy mirror those of the [Dropdown Form Field Display](../FormFieldDisplayStrategies/Dropdown.md) strategy.  

- `values` (array)  
    A list of option values to use for the dropdown.

- `value_source` (string)  
    A fully qualified class name for an [enum](https://github.com/myclabs/php-enum) or `Czim\CmsModels\Contracts\View\DropdownStrategyInterface` instance.  
    If set, this overrules the main `values` property list.

- `labels` (array, associative)  
    A list of labels to display, keyed by the option value.    
    This value is ignored if `labels_translated` is used.  
        
- `labels_translated` (array, associative)  
    A list of translation keys to use as labels, keyed by the option value.  
    If set, this overrides `labels`.
    
- `label_source` (string)    
    A fully qualified class name for a `Czim\CmsModels\Contracts\View\DropdownStrategyInterface` instance.  
    If set, this overrules the `labels` and `labels_translated` lists.

See the [DropdownStrategyInterface](https://github.com/czim/laravel-cms-models/tree/master/src/Contracts/View/DropdownStrategyInterface.php).


Example:

```php
    'options' => [
        
        'values' => [
            'alpha',
            'beta',
            'gamma',
        ],
        
        'labels_translated' => [
            'alpha' => 'special.options.alpha',
            'beta'  => 'special.options.beta',
            'gamma' => 'special.options.gamma',
        ],
    ]
```
