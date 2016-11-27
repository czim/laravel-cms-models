# Form Field Display Strategy: WYSIWYG text editor
 
This strategy renders a CKEditor text editor.


## Options

By default, the `cms-models.ckeditor` settings are used for CKEditor fields.
The config may be overridden using the following options: 

- `config` (string)  
    Path to a custom CKEditor configuration file, relative to `public/_cms/ckeditor/`. The `.js` extension may be omitted.  
    Available configurations: `default`, `small`, `minimal`.

- `collapse_toolbar` (boolean)  
    If set to `true`, starts with the toolbar collapsed, overriding the current config file.

 
 
 ```php
     'options' => [
     
         // The custom CKEditor config file to use
         'config' => 'minimal',
         
         // Whether the toolbar should start collapsed
         'collapse_toolbar' => true,
         
     ]
 ```

