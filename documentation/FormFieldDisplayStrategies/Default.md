# Form Field Display Strategy: Default

Default simple text input fields, used as a fallback when no strategy is specified.

## Options

The type, maxlength and size properties of the input are determined by model analysis, but can be overridden.

- `type` (string)  
    The type attribute for the input tag (`text`, `email`, `password`, etc).  
    If not set, defaults to `text`.

- `size` (integer)  
    The size attribute for the input tag.

- `maxlength` (integer)  
    The maxlength attribute for the input tag.

- `min` (integer)  
    The min attribute for the input tag.
    
- `max` (integer)  
    The max attribute for the input tag.
    
- `step` (integer)  
    The step attribute for the input tag.
    
- `pattern` (string)  
    The regex pattern attribute for the input tag.




```php
    'options' => [
        'type'      => 'email',   
        'size'      => 4,
        'maxlength' => 8,
    ]
```

