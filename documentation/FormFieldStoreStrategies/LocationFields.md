# Form Field Store Strategy: Location Fields

This strategy stores values for a typical location picker, such as a latitude, longitude and a string representation of a location.

These values may be disabled using the options, if not all fields are present on the model (or should just not be stored/retrieved from it).

This may be used in combination with the
[Location display strategy](../FormFieldDisplayStrategies/Location.md).

Expects an array like so:

```
    [
        'latitude'  => 4.497009700000035,
        'longitude' => 52.1601144,
        'location'  => '2312 HZ Leiden, Netherlands',
    ]
```


## Options

- `latitude_name` (string or bool)
    The attribute name on which the latitude should be set.  
    Set to `false` to disable saving the latitude on the model.  
    Default: `'latitude'`.
    
- `longitude_name` (string or bool)
    The attribute name on which the longitude should be set.  
    Set to `false` to disable saving the longitude on the model.  
    Default: `'longitude'`.

- `location_name` (string or bool)
    The attribute name on which the location string should be set.  
    Set to `false` to disable saving the location string representation on the model.  
    Default: `'location'`.
