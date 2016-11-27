# Form Field Display Strategy: Dropdown
 
This strategy renders a dropdown select field with customizable option content.

It submits and expects a (string) value and does not require a custom store strategy.


## Options

For `enum` table fields, the available options to populate the select with are filled automatically.  
For other fields, the options must be defined through the `options.values` key.

- `values` (array)  
    A list of option values to use for the dropdown.

- `value_source` (string)  
    A fully qualified class name for an [enum](https://github.com/myclabs/php-enum) or `Czim\CmsModels\Contracts\View\DropdownStrategyInterface` instance.  
    If set, this overrules the `values` list.

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
 
 
 ```php
     'options' => [
     
         // PHP date format 
         'options' => 'Y-m-d H:i:s',
         
         // MomentJS date format
         'moment_format' => 'YYYY-MM-DD HH:mm:ss',
         
     ]
 ```

