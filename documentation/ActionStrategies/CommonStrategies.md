## Edit

Link to edit form for current record.


## Show

Link to show page for current record. 


## Children

Link to model listing for children of the current record.

### Options

- `model` (string)  
    Eloquent model, the target child model for which the listing should be displayed.

- `relation` (string)  
    Eloquent relation name, on the child model that refers back to this parent model.


```php
    'options' => [
        
        // The Eloquent relation method name on the child model
        // ie. the model for which the children list should be displayed
        'relation' => 'parentModel',
        
        // Child model, which has the relation method defined in these options.
        'model' => App\Models\ChildModel::class,
        
    ]
```
