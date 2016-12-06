# Form Field Display Strategy: Attachment Stapler Image
 
This strategy renders a file browse/upload field with a link preview to the currently uploaded file. Only works with Stapler fields with image-only content.
 
Displays a lightbox preview for the original image size.

It submits a file upload, and expects a Stapler attachment.


## Options

- `accept` (string)  
    The file upload input `accept` attribute value.  
    Defaults to: `'image/*'`.

 
 ```php
     'options' => [
     
         // Accept attribute input value
         'accept' => 'image/jpeg|image/png',

     ]
 ```

