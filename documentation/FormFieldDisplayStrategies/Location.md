# Form Field Display Strategy: Location

This strategy uses a GoogleMaps API picker plugin to allow selecting a location with latitude and logitude values.
 
It renders an autocomplete location input field as well as lat/long fields, and a map picker using the [Logicify jQuery Locationpicker](https://github.com/Logicify/jquery-locationpicker-plugin) plugin.

It submits an array with `latitude`, `longitude` and a `location` string to be stored with the [LocationFields store strategy](../FormFieldStoreStrategies/LocationFields.md).

For this to work, the `cms-models.api-keys.google-maps` configuration key must be set to a valid Google Maps API key.


## Options

- `default_latitude` (string)  
    A default latitude to set if the field is empty on page load.
    
- `default_longitude` (string)  
    A default longitude to set if the field is empty on page load.

- `default_location` (string)  
    A default location string to set if the field is empty on page load.

- `default` (bool)  
    If `default_latitude` and `default_longitude` are not set, setting this `true` will use as defaults the values set for the configuration keys:  
    `cms-models.custom-strategies.location.default.location`,  
    `cms-models.custom-strategies.location.default.latitude` and  
    `cms-models.custom-strategies.location.default.longitude`.


