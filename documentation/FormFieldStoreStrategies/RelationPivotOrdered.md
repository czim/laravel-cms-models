
# Form Field Store Strategy: RelationPivotOrdered

This strategy stores plural relation keys for `BelongsToMany` relations, including a single pivot position column.

This may be used in combination with the
[RelationPivotOrderable display strategy](../FormFieldDisplayStrategies/RelationPivotOrderable.md).
 
Data is expected and retrieved as key-value pairs: the key being the related model key, the value the position.

Position is ascending, starting at 1.


## Options

It is generally not necessary to set options for this strategy if the related model is part of the CMS.

- `position_column` (string, default: `'position'`)  
    The pivot table column used for ordering records. New order positions will be saved to this column.

 ```php
     'options' => [
     
         // The position & sorting column
         'position_column' => 'order',
     ]
```
