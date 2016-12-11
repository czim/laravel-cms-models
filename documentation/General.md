# General Information

## Model keys

Models are stored by a key that is made by 'sluggifying' their absolute namespace.  
The model `App\Models\Post` would have the model key: 'app-models-post'.

To get information about a specific model, you may use the model key for the `cms:model:show` artisan command. For example:

```bash
php artisan cms:models:show app-models-post
```

If the model key parameter is omitted, all model information is displayed.


## Modules

This CMS package generates separate CMS module instances for each model.   
The keys for the modules are based on the model keys, as follows:
 
    'models.' + model key   

The module for the `App\Models\Post` model would have the module key: 'models.app-models-post'.


## Permissions

Each model module has its own permissions. This means that permissions can be assigned to users separately for each model.

Models have the following permissions:

- `create`: create new records
- `edit`: edit existing records
- `delete`: delete records

A user with *any* permissions for the model (ie. checks for `<model key>.*` permission). If no other permissions should be granted, it is recommended to use `.show` for consistency.

The permissions are prefixed as follows:

    'models.' + model key + '.'
    
So for the model `App\Models\Post` this would make, for instance:

    models.app-models-post.create


## Caching Model Information

The CMS analyzes the models and loads custom model configuration files for models it should include. Once this is done, the information is cached, unless the config option `cms-models.repository.cache` is set to `false`. The CMS cache driver is used.
  
If the cache is used, model information is not rebuilt as long as the cache is set. This means that any model configuration changes made will not take effect until the cache is cleared manually (or by expiring naturally).

To clear the model information cache, use the following command:

```bash
php artisan cms:models:clear
```
