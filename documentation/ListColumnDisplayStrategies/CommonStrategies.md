## Default

This strategy is used if none has been configured for a list column.
Displays attribute content as-is.


## Check

Displays a simple green check mark or red cross, depending on whether the source content is true or false (cast as a boolean). 


## CheckNullable

Displays a simple green check mark or red cross, depending on whether the source content is true or false (cast as a boolean).  
If the value is null, neither is shown, leaving the cell empty.


## Date

Displays a `DateTime` value formatted to show only the date.

### Options

- `format` (string)  
    Any PHP date format. Defaults to `'Y-m-d'`.
    
```php
    'options' => [
        // The date format to render the date source in
        'format' => 'd/m/Y',
    ]
```


## DateTime

Displays a `DateTime` value formatted to show date and time.

### Options

- `format` (string)  
    Any PHP date format. Defaults to `'Y-m-d H:i'`.
    
```php
    'options' => [
        // The date format to render the date source in
        'format' => 'd/m/Y H:i:s',
    ]
```

## Time

Displays a `DateTime` value formatted to show only the time.

### Options

- `format` (string)  
    Any PHP date format. Defaults to `'H:i'`.
    
```php
    'options' => [
        // The date format to render the date source in
        'format' => 'H:i:s',
    ]
```


## Stapler File

Displays a Stapler upload field as the filename, with a link to the uploaded file.


## Stapler Image

Displays a Stapler upload field for images as a thumbnail.
This uses the smallest available stapler resize by default. 

### Options

- `stapler_style` (string)  
    The stapler resize style to use.  
    If not set, defaults to using the smallest available. 
    
```php
    'options' => [
        // The stapler resize style to use
        'stapler_style' => 'thumbnail',
    ]
```
