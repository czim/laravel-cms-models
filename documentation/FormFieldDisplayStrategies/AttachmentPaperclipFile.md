# Form Field Display Strategy: Attachment Paperclip File
 
This strategy renders a file browse/upload field with a link preview to the currently uploaded file. Only works with Paperclip fields. 

It submits a file upload, and expects a Paperclip attachment.

If the [File Upload Module](https://github.com/czim/laravel-cms-upload-module) is installed, this strategy will use it and upload files using AJAX.

The submitted value is an array with `keep` (boolean), `upload` (if not using AJAX), and `upload_id` (when using AJAX).


## Options

- `accept` (string)  
    The file upload input `accept` attribute value.  
    Omitted by default. 
    
- `validation` (array of strings)  
     The validation rules to apply to the file upload.
     The `nullable` and `required` rules need not be included, these will be determined automatically.
     
- `no_ajax` (boolean)  
    Set to `true` if AJAX uploading should be disabled even if the upload module is loaded.

 
 ```php
     'options' => [
     
         // Accept attribute input value
         'accept' => 'image/*',
         
         // Validation rules for the image upload
         'validation' => [
            'mimetypes:application/json',
         ],

         // Disable AJAX uploading even if the upload module is loaded.
         'no_ajax' => true,
     ]
 ```

