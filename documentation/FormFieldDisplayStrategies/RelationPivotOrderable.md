
# Form Field Display Strategy: RelationPivotOrderable

This strategy renders a list of connected records through a `BelongsToMany` relation, sorted by a position column.
The connected records may be removed or dragged and dropped to reorder them.
An autocomplete select field and add button combo allow the user to add new connections.

It submits an array with model keys that may be stored with the [RelationPivotOrdered store strategy](../FormFieldStoreStrategies/RelationPivotOrdered.md).

This only allows models to be connected once, without duplicate keys. 

## Options

It is generally not necessary to set options for this strategy if the related model is part of the CMS.
In any case these options may still be overridden to specificy specific reference behavior.

- `source` (string)
    If not set, the model's default reference source is used (if it is part of the CMS). 
    Otherwise, the model's key will be used.
     
- `target` (string)
    If not set, `source` value is used.
    Indicates the column(s) used for searching/matching references using autocomplete input.
    
- `strategy` (string)
    Strategy identifier for a [model reference strategy](../Strategies.md#model-reference-strategies). The `source` value is used for this strategy.
    If not set, the model's default reference strategy is used (if it is part of the CMS). 
    Otherwise, the model's key will be displayed.
    This determines how the related models are referenced as displayed options.
 
 - `context_strategy` (string)
    Strategy identifier for a [repository context strategy](../Strategies.md#repository-context-strategies).
     If not set, the model's default `context_strategy` is used (if it is part of the CMS). 
     Otherwise, only `withoutGlobalScopes()` will be applied to the query.
     This allows you to (un)restrict the models that will be available as options for the select input.

- `sort_direction` (string: `asc` or `desc`)
    This determines the direction the displayed model references will be sorted in. This will only work for table columns directly belonging to the model, or columns on related translation models.
    Other sources are currently not supported, and will not be used for sorting references.
    Note that this only affects the dropdown results, not the list of connected references (which are sorted by position).
    
- `parameters` (array, associative)
    Optional parameters that may be required by the `strategy`.
    
    
 
 ```php
     'options' => [
     
         // The source column(s) to use for the reference strategy 
         'source' => 'title',
         
         // The target column(s) to search when using autocomplete search strings
         'target' => 'title,name,id',
         
         // The reference strategy to use.
         'strategy' => SomeReferenceStrategyClass::class,
         
         // The query context strategy to use.
         'context_strategy' => 'OnlyActive',
         
         // Sorting direction for references displayed.
         `sort_direction` => 'desc',
     ]
 ```

