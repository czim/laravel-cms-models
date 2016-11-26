# Form Field Store Strategy: RelationSingleMorph

This strategy stores a single relation model/key combination for `MorphTo` relations.
No other relations are supported.

This may be used in combination with the
[RelationSingleMorphDropdown](../FormFieldDisplayStrategies/RelationSingleMorphDropdown.md) 
or [RelationSingleMorphAutocomplete](../FormFieldDisplayStrategies/RelationSingleMorphAutocomplete.md)
display strategies.

This retrieves and stores a model connection as a combination of model class and model key,
as as string, concatenated by a colon: `<class>:<key>`.


## Options

It is generally not necessary to set options for this strategy if the morphable related models are all part of the CMS.
 

- `models` (array, associative, keyed by model class FQN)  
    Set this to configure which models are selectable in the dropdown or the autocomplete search results.  
    If the model is not present as a key in this list, its instances will not be selectable 
    and no references will be retrieved for autocomplete searches.


```php
    'options' => [
    
        // Polymorphic related models
        'models' => [
            \App\Models\SomeModel::class => [
                // The reference source for this model
                'source' => 'title',
                // The autocomplete lookup column for this model
                'target' => 'title,first_name,last_name',
            ],
            \App\Models\AnotherModel::class => [
                // Optional specific configuration for this model
                // ...
            ],
        ], 
    ]
```
