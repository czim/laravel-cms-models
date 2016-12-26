# Form Strategy Reference

The available form display and store strategies are listed below.

This list is not exhaustive; custom strategy classes and aliases may be created freely.


## Form Display Strategies

### Properties

- [**`Default`**](FormFieldDisplayStrategies/Default.md)  
    The default fallback: simple text input field.

- [**`Colorpicker`**](FormFieldDisplayStrategies/Colorpicker.md)  
    Colorpicker widget for a web/css color value.

- [**`Datepicker`**](FormFieldDisplayStrategies/Datepicker.md)  
    Datepicker widget for date & time values.
    
- [**`Dropdown`**](FormFieldDisplayStrategies/Dropdown.md)  
    Dropdown select with customizable options.
    
- [**`Static`**](FormFieldDisplayStrategies/Static.md)  
    Static value display, optionally with list strategy.
    
- [**`Wysiwyg`**](FormFieldDisplayStrategies/Wysiwyg.md)  
    WYSIWYG rich text editor (HTML) field.
   

### Files

- [**`AttachmentStaplerFile`**](FormFieldDisplayStrategies/AttachmentStaplerFile.md)  
    File upload field for [Stapler](https://github.com/CodeSleeve/laravel-stapler) attribute.
    
- [**`AttachmentStaplerImage`**](FormFieldDisplayStrategies/AttachmentStaplerImage.md)  
    File upload field for [Stapler](https://github.com/CodeSleeve/laravel-stapler) image attribute, with preview.


### Relations

#### Singular Relations

- [**`RelationSingleDropdown`**](FormFieldDisplayStrategies/RelationSingleDropdown.md)  
    Simple multiple select field for `BelongsTo`, `HasOne`, `MorphOne`.


- [**`RelationSingleAutocomplete`**](FormFieldDisplayStrategies/RelationSingleAutocomplete.md)  
    Autocomplete select for `BelongsTo`, `HasOne`, `MorphOne`.


- [**`RelationSingleMorphDropdown`**](FormFieldDisplayStrategies/RelationSingleMorphDropdown.md)  
    Simple multiple select field for `MorphTo`.


- [**`RelationSingleMorphAutocomplete`**](FormFieldDisplayStrategies/RelationSingleMorphAutocomplete.md)  
    Autocomplete select field for `MorphTo`.


#### Plural Relations

- [**`RelationPluralMultiselect`**](FormFieldDisplayStrategies/RelationPluralMultiselect.md)  
    Simple multiple select field for `BelongsToMany`, `HasMany`, `MorphMany`.


- [**`RelationPluralAutocomplete`**](FormFieldDisplayStrategies/RelationPluralAutocomplete.md)

    Autocomplete select field for `BelongsToMany`, `HasMany`, `MorphMany`.


- [**`RelationPivotOrderable`**](FormFieldDisplayStrategies/RelationPivotOrderable.md)  
    Drag-and-drop orderable list of connections to related models, with autocomplete select field for adding new connections.  
    For `BelongsToMany` with integer position column on the pivot table.


## Form Store Strategies

### Properties

- [**`Default`**](FormFieldStoreStrategies/Default.md)  
    Default fallback simple direct model storage.


- [**`Date`**](FormFieldStoreStrategies/Date.md)  
    For date time values (`timestamp`, `date`, `datetime`, `time`).

- [**`PasswordUpdate`**](FormFieldStoreStrategies/PasswordUpdate.md)  
    For storing hashed passwords, but only if entered.


### Relations

- [**`RelationSingleKey`**](FormFieldStoreStrategies/RelationSingleKey.md)  
    For keys to models related using `BelongsTo`, `HasOne`, `MorphOne`.


- [**`RelationPluralKeys`**](FormFieldStoreStrategies/RelationPluralKeys.md)  
    For arrays of keys to models related using `BelongsToMany`, `HasMany`, `MorphMany`.


- [**`RelationSingleMorph`**](FormFieldStoreStrategies/RelationSingleMorph.md)  
    For combinations of model & key to models related using `MorphTo`.


- [**`RelationPluralMorph`**](FormFieldStoreStrategies/RelationPluralMorph.md)  
    For arrays of combinations of model & key to models related using `MorphToMany`.
