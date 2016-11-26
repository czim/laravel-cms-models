# Form Field Store Strategy: Date

This strategy stores a string-formatted date format.

This may be used in combination with the
[Datepicker display strategies](../FormFieldDisplayStrategies/Datepicker.md).

Expects a string date(time) value that may be converted to a `Carbon` instance.
If set, the `format` option described below is used for this.


## Options

- `format` (string)  
    If not set uses `'Y-m-d H:i'`, `'Y-m-d'`, or `'H:i'` for DateTime, Date and Time strategies respectively.  
    Any PHP date format is allowed.

 ```php
     'options' => [
     
         // PHP date format 
         'format' => 'Y-m-d H:i:s',
     ]
```
