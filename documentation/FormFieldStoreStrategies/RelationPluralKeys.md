
# Form Field Store Strategy: RelationPluralKeys

This strategy stores plural relation keys for `HasMany`, `MorphMany`, `BelongsToMany` relations.
Though not intended to be used for them, it will still work with `HasOne`, `MorphOne` and `BelongsTo` relations.

This may be used in combination with the
[RelationPluralMultiselect](../FormFieldDisplayStrategies/RelationPluralMultiselect.md) 
or [RelationPluralAutocomplete](../FormFieldDisplayStrategies/RelationPluralAutocomplete.md)
display strategies. 

## Options

It is generally not necessary to set options for this strategy if the related model is part of the CMS.
 
### Detaching HasOne, HasMany, MorphOne and MorphMany

When handling relations for One/Many relations, the foreign keys are on the related model,
making it necessary to manually detach models to keep the relation in sync with the 
submitted form values.

If models were related, but not part of the submitted values, they can be detached.
This can be done either by setting their foreign keys to `NULL`, if the foreign keys are nullable.
Otherwise, detaching can only be done by deleting the related model instead.

If the related model is part of the CMS, then its model information will be used to determine whether the keys are nullable. 
If they are, detaching is done automatically by nullifying the foreign keys.
If the related model is not part of the CMS, no attempt is made to determine whether the keys are nullable.

Either way, the behavior may be configured by setting the following `options` keys for the form field's data:

- `detach` (boolean, default: `true`)  
    Set this to `false` to disable detaching models entirely.
    
- `nullable_keys` (boolean or `null`, default: `null`)  
    Set this to `true` to force detaching by nullifying keys.  
    If set to `null`, CMS model data will be used to determine whether nullifying keys is possible.
    
- `delete` (boolean, default: `false`)  
    If set to `true`, will allow deleting models to detach them.  
    Obviously, it is really important to be careful with this, as it can easily result in data loss.


```php
    'options' => [
    
        // Whether it is allowed to detach HasOne, HasMany, MorphOne, MorphMany
        // related records that are not explicitly set to be related anymore after an update.
        'detach' => true,
 
        // Whether the keys of the related models are nullable
        // (only required if the related model is not part of the CMS)
        'nullable_keys' => true,
        
        // Whether it is allowed to delete HasOne, HasMany, MorphOne, MorphMany
        // related records that are not explicitly set to be related anymore after an update,
        // in the case they are not detachable by setting key to null.
        'delete' => false,   
    ]
```
