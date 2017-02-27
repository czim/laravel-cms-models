# Form Field Display Strategy: WYSIWYG text editor
 
This strategy renders a CKEditor text editor.


## Options

By default, the `cms-models.ckeditor` settings are used for CKEditor fields.
The config may be overridden using the following options: 

- `config` (string)  
    Path to a custom CKEditor configuration file, relative to `public/_cms/ckeditor/config/`. The `.js` extension may be omitted.  
    Available configurations: `default`, `small`, `minimal`.

- `collapse_toolbar` (boolean)  
    If set to `true`, starts with the toolbar collapsed, overriding the current config file.

- `editor_options` (array)  
    A set of key-value pairs to set specific CKEditor configuration options. For possible options, 
    check out the [http://docs.ckeditor.com/#!/api/CKEDITOR.config](CKEDITOR.config documentation).
 
 
 ```php
     'options' => [
     
         // The custom CKEditor config file to use
         'config' => 'minimal',
         
         // Whether the toolbar should start collapsed
         'collapse_toolbar' => true,
         
         // Specific CKEditor options
         'editor_options' => [
            'height' => '200px',
         ],
     ]
 ```

