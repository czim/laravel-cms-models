# Form Field Display Strategy: Datepicker

Three datepicker display strategies are included:
 
- `DateTime`: formatted as '2016-01-01 23:40'
- `Date`: formatted as '2016-01-01'
- `Time`: formatted as '23:40'

This strategy renders an input field with a datepicker, using the [Bootstrap 3 Datepicker](http://eonasdan.github.io/bootstrap-datetimepicker/).

It submits a date (or time) formatted string that may be stored with the [Date store strategy](../FormFieldStoreStrategies/Date.md).

## Options

Without any configuration, the formats used are as indicated above.

A custom date format may be set for PHP, and optionally a specific one for MomentJS.
PHP date formats are automatically converted to Moment date formats, insofar possible. 
In most cases, it is not necessary to set the Moment date format manually.

- `format` (string)
    If not set uses `'Y-m-d H:i'`, `'Y-m-d'`, or `'H:i'` for DateTime, Date and Time strategies respectively.
    Any PHP date format is allowed.
     
- `moment_format` (string)
    If not set, `format` value is converted to match.
    For some special PHP date formats, it may be necessary to enter the Moment equivalent manually through this option.
    
    
 
 ```php
     'options' => [
     
         // PHP date format 
         'format' => 'Y-m-d H:i:s',
         
         // MomentJS date format
         'moment_format' => 'YYYY-MM-DD HH:mm:ss',
         
     ]
 ```

