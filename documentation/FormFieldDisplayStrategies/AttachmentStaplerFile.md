# Form Field Display Strategy: Attachment Stapler File
 
This strategy renders a file browse/upload field with a link preview to the currently uploaded file. Only works with Stapler fields. 

It submits a file upload, and expects a Stapler attachment.

If the [File Upload Module](https://github.com/czim/laravel-cms-upload-module) is installed, this strategy will use it and upload files using AJAX.

The submitted value is an array with `keep` (boolean), `upload` (if not using AJAX), and `upload_id` (when using AJAX).


## Options

- `accept` (string)  
    The file upload input `accept` attribute value.  
    Omitted by default. 

 
 ```php
     'options' => [
     
         // Accept attribute input value
         'accept' => 'image/*',

         // Disable AJAX uploading even if the upload module is loaded.
         'no_ajax' => true,
     ]
 ```

