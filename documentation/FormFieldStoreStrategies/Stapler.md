# Form Field Store Strategy: Stapler

This strategy stores values for the stapler attachment file or image strategies.
 
 If the [File Upload Module](https://github.com/czim/laravel-cms-upload-module) is installed, this strategy will use it and use the `upload_id` to look up the AJAX-uploaded file.


Expects an array like so:

```
    [
        'keep'       => false,
        'upload'     => null,
        'upload_id'  => 13,
    ]
```


## Options


- `validation` (array of strings)  
     The validation rules to apply to the file upload.
     The `nullable` and `required` rules need not be included, these will be determined automatically.
     
- `no_ajax` (boolean)  
    Set to `true` if AJAX uploading should be disabled even if the upload module is loaded.  
    Expects the `upload` value to be a file and ignores `upload_id`, even if the upload module is loaded.

