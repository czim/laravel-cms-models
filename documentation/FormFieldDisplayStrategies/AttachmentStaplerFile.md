# Form Field Display Strategy: Attachment Stapler File
 
This strategy renders a file browse/upload field with a link preview to the currently uploaded file. Only works with Stapler fields. 

It submits a file upload, and expects a Stapler attachment.


## Options

- `accept` (string)  
    The file upload input `accept` attribute value.  
    Omitted by default. 

 
 ```php
     'options' => [
     
         // Accept attribute input value
         'accept' => 'image/*',

     ]
 ```

