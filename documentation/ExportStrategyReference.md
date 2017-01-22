# Export Strategy Reference

The available export and export column strategies are listed below.

This list is not exhaustive; custom strategy classes and aliases may be created freely.


## Export Strategies

Strategies exporting model listings.

- [**`Csv`**](ExportStrategies/CommonStrategies.md#csv)  

- [**`Xml`**](ExportStrategies/CommonStrategies.md#xml)  
      
- [**`Excel`**](ExportStrategies/CommonStrategies.md#excel)  
    

## Export Column Strategies

Strategies that determine the rendering of model attributes in columns (or, say, XML properties) in exported model listing data.

- [**`Default`**](ExportColumnStrategies/CommonStrategies.md#default)  
    Simple string representation.
    
- [**`BooleanString`**](ExportColumnStrategies/CommonStrategies.md#boolean-string)   
    Simple 'true' or 'false' representation of a boolean.

- [**`Date`**](ExportColumnStrategies/CommonStrategies.md#date)  
    Formatted date string.
    
- [**`StaplerFileLink`**](ExportColumnStrategies/CommonStrategies.md#stapler-file-link)  
    Stapler field as URI.

- [**`TagList`**](ExportColumnStrategies/CommonStrategies.md#tag-list)  
    Comma-separated list of tags for Taggable models.
