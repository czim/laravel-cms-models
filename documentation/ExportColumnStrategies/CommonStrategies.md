## Default

This strategy is used if none has been configured for an export column.
Displays attribute content as-is.


## Boolean String

Displays a boolean value as a `'true'` or `'false'` string.


## Date

Displays a `DateTime` value formatted according to an optional format.

### Options

- `format` (string)  
    Any PHP date format. Defaults to `'Y-m-d H:i:s'`.
    
```php
    'options' => [
        // The date format to render the date source in
        'format' => 'd/m/Y',
    ]
```

## Paperclip File Link

Displays a Paperclip attribute as an URI to the uploaded file.


## Tag List

Displays a comma-separated list of tags for a model with the [Taggable trait](https://github.com/rtconner/laravel-tagging). 
