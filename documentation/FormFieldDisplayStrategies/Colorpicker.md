# Form Field Display Strategy: Colorpicker

A color picker for picking a single (web) color.

This strategy renders an input field using the [Bootstrap Colorpicker](https://itsjavi.com/bootstrap-colorpicker/).

It submits and expects a formatted color string.


## Options

- `format` (string)  
    The color format to use: `'hex'`, `'rgb'`, or `'rgba'`.  
    If not set, lets the picker widget select the format.  
    
     
### Example Options
 
 ```php
     'options' => [
     
         // Color string format 
         'format' => 'hex',

     ]
 ```

