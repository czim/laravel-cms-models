# Model Configuration: Export Configuration

Export strategies may be configured to offer a button-click export for model listing data in various formats.

By default, no export strategies are enabled for a model. 
Set the configuration as described here to enable this option for users. 


## Strategies

- `strategy` (string)  
    The strategy that is used to perform the export.

- `label` (string)  
    Explicitly label to show on the export button or link.
        
- `label_translated` (string)  
    The translation key for a CMS translation to use as the export button or link text.
    If set, this overrides the `label`.

- `permissions` (`false`, string or array of strings)  
    The permission(s) required to be able to see use this export strategy.
    If more than one, users must have all permissions in order to use it.  
    If `false` or left unset, will default to the model's own '.export' permission.  
    Note that any *custom* permissions added here will not automatically be known by the CMS (or ACL module). For that, they must be added to the CMS configuration. For more information, check the documentation for your chosen [ACL module](https://github.com/czim/laravel-cms-acl-module).
    
- `options` (array, associative)  
    Options pertaining to the export strategy used.


### Available Strategies

Strategies for exporting data that are included in the CMS:

- `csv`  
    Simple comma-separated-values file export.  
    
- `xml`
    Basic XML-data export.  
    
- `excel`  
    Excel file export.
    
  
See [the export strategy documentation](../ExportStrategyReference.md#export-strategies) for more information about export strategies and package installations required for them to work.

## Columns

Export columns (or whatever the model attribute values are rendered for a given export strategy) may be included or overridden in the `export.columns` array.

In addition, each export strategy may have its own column configuration, overriding these defaults. 
This may be done by setting the `export.strategies.<strategy key>.columns` array.


### Including columns

The CMS will generate sensible default column data in most cases, and is set to display most columns automatically.

To use the defaults for a specific set of columns to include at the exclusion of others, add them as string values in the columns array:
 
 ```php
    'columns' => [
        'title',
        'body',
        'genre',
        'tags',
    ]
 ```

If columns are defined, only the ones included in the configuration will be displayed.
 

### Overriding columns

Keys that may be set:

- `strategy` (string)  
    The strategy that is used to render this column's values.

- `source` (string)  
    Indicates the source to be used for the data shown using this field. Default: field entry's key.

- `label` (string)  
    Explicitly label to show as the column header.
        
- `label_translated` (string)  
    The translation key for a CMS translation to use as the column header.
    If set, this overrides the `label`.

- `options` (array, associative)  
    Options pertaining to the strategy used.

- `hide` (boolean)  
    If `true`, hides the column from the export entirely.
    Mainly useful for hiding columns without having to remove the settings entirely.
    

### Column options

Each column definition may have an `options` associative array value.
What keys may be set is determined by the strategies (`strategy`) for the export column.
More information on this may be found in the documentation of the [export column strategies](../ExportStrategyReference.md#export-column-strategies).
