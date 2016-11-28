# List Strategy Reference

The available list display and filter strategies are listed below.

This list is not exhaustive; custom strategy classes and aliases may be created freely.


## List Display Strategies

Strategies for displaying content of model listing table cells.

### Properties

- [**`Default`**](ListColumnDisplayStrategies/CommonStrategies.md#default)  
    The default fallback: simple as-is attribute display.
    
- [**`Check`**](ListColumnDisplayStrategies/CommonStrategies.md#check)  
    Displays a checkbox icon based on a boolean value, interpreting `null` as `false`.
    
- [**`CheckNullable`**](ListColumnDisplayStrategies/CommonStrategies.md#checknullable)  
    Displays a checkbox icon based on a boolean value, not displaying anything for `null` values.
      
- [**`Date`**](ListColumnDisplayStrategies/CommonStrategies.md#date)  
    Displays a formatted date value.
    
- [**`DateTime`**](ListColumnDisplayStrategies/CommonStrategies.md#datetime)  
    Displays a formatted datetime value.

- [**`Time`**](ListColumnDisplayStrategies/CommonStrategies.md#time)  
    Displays a formatted datetime value as time only.

- [**`StaplerFile`**](ListColumnDisplayStrategies/CommonStrategies.md#stapler-file.md)  
    Displays a stapler file name as a link.
    
- [**`StaplerImage`**](ListColumnDisplayStrategies/CommonStrategies.md#stapler-image)  
    Displays a stapler image thumbnail.


### Relations

- [**`RelationCount`**](ListColumnDisplayStrategies/RelationStrategies.md#relationcount)  
    Displays a plural relation as a simple count value.
    
- [**`RelationReference`**](ListColumnDisplayStrategies/RelationStrategies.md#relationreference)  
    Displays a single relation as a model reference string.



## Filter Strategies

Strategies that determine the display and application of filters for a model listing.

### Properties

- [**`BasicString`**](FilterStrategies/BasicString.md)  
    Simple string filter that searches for loosy, unsplit `%search term%` matches.
    
- [**`BasicSplitString`**](FilterStrategies/BasicSplitString.md)   
    Simple string filter that searches for loosy, split `%search% AND %term%` matches.

- [**`DropdownBoolean`**](FilterStrategies/DropdownBoolean.md)  
    For filtering on boolean attributes with a select dropdown.
    
- [**`DropdownEnum`**](FilterStrategies/DropdownEnum.md)  
    For filtering on enum values with a select dropdown.
