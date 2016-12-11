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

- `view_mode` (string)  
    One of 'years', 'decades', 'months' or 'days'. ('days' is default).
    Useful for setting a birthday picker with 'years', for instance.

- `minimum_date` (string)  
    A date indicator as documented below.
    Indicates the earliest date that may be selected.
    
- `maximum_date` (string)  
    A date indicator as documented below.
    Indicates the latest date that may be selected.
    
- `excluded_dates` (array of strings)  
    An array of date indicators as documented below.
    Indicates dates that may not be selected.


### Date Indicator

A date indicator is a string that lets the CMS determine absolute or relative dates.  

It may be:

- The string `'now'`.  
    To indicate today's date (or time).

- A [PHP DateInterval interval_spec](http://php.net/manual/en/dateinterval.construct.php) string, preceded by `+` or `-`.  
    To indicate a date or time relative to the present date.
    
- A date time string of any `strtotime`-able format.  
    To indicate an absolute date or time.


### Example Options
 
 ```php
     'options' => [
     
         // PHP date format 
         'format' => 'Y-m-d H:i:s',
         
         // MomentJS date format
         'moment_format' => 'YYYY-MM-DD HH:mm:ss',
         
         // Date range to restrict the datepicker to
         'minimum_date' => '-P3M', // 3 months before today
         'maximum_date' => '+P2M', // 2 months after today
         
         // Dates to excluded as non-selectable
         'excluded_dates' => [ '2016-01-01', '-P2D' ],
     ]
 ```

