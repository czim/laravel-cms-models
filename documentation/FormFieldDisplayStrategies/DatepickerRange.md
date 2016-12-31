# Form Field Display Strategy: Datepicker Range

This strategy combines two input fields to make a typical date range.
 
This strategy renders two input fields with a datepicker, using the [Bootstrap 3 Datepicker](http://eonasdan.github.io/bootstrap-datetimepicker/).

It submits two date formatted strings in an array that may be stored with the [DateRange store strategy](../FormFieldStoreStrategies/DateRange.md).


## Options

A custom date format may be set for PHP, and optionally a specific one for MomentJS.
PHP date formats are automatically converted to Moment date formats, insofar possible. 
In most cases, it is not necessary to set the Moment date format manually.

- `from_required` (bool)  
    Whether the 'from' field may be left empty, for a range with an open start.
    
- `to_required` (bool)  
    Whether the 'to' field may be left empty, for an open-ended range.

- `format` (string)  
    If not set uses `'Y-m-d H:i'`.    
    Any PHP date format is allowed. The format will be the same for both inputs.
     
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

For more information on formats and date indicators, see the [Datepicker strategy documentation](Datepicker.md).
