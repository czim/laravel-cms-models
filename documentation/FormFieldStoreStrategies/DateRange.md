# Form Field Store Strategy: Date Range

This strategy stores two string-formatted date formats, to two separate fields: a 'from' and a 'to' field.

This may be used in combination with the
[Datepicker Date Range strategy](../FormFieldDisplayStrategies/DatepickerRange.md).

Expects an array with two values, keyed by `'to'` and `'from'`, like so:

```
    [
        'from' => '2017-01-01', 
        'to'   => '2018-01-01' 
    ]
```

Both should contain a string date(time) value that may be converted to a `Carbon` instance.
If set, the `format` option described below is used for this.


## Options

- `from` (string)  
    The attribute on the model that stores the start date of the range.

- `to` (string)  
    The attribute on the model that stores the end date of the range.

- `format` (string)  
    If not set uses `'Y-m-d H:i'`.  
    Any PHP date format is allowed. Both fields use the same format.

 ```php
     'options' => [
     
         // The attributes to retrieve and store the date values for the range from.
         'from' => 'date_start',
         'to'   => 'date_end',
     
         // PHP date format 
         'format' => 'Y-m-d H:i:s',
     ]
```
