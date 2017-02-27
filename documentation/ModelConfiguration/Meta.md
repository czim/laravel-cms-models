# Model Configuration: General & Meta Data


## Meta section

The `meta` section stores overriding information for general handling of model-related requests.

To set a different controller for either web or API routes, set the FQN for the controller class in `meta.controller` and/or `meta.controller_api` respectively.

By default, global scopes are disabled when accessing models for the CMS. To prevent this, set `meta.disable_global_scopes` to `false`.

The repository can be set up by defining a strategy class reference (or alias) in `meta.repository_strategy`.
See [Repository Context Strategies](../Strategies.md#repository-context-strategies) for more information on the strategies that set the repository's context.

Example:

```php
<?php
return [
    'meta' => [
        'controller'            => \App\Http\Controllers\Cms\YourController::class,
        'disable_global_scopes' => false,        
    ],
    
    // ...    
];
```


## Model Reference

You can define how models will be referred to within the CMS, by setting the `reference` value (top level).

```php
<?php
return [
    'reference' => [
        'source'   => 'title',
        'strategy' => \Czim\CmsModels\View\ReferenceStrategies\IdAndAttribute::class,
    ],
    
    // ...
];
```

Keys that may be set:

- `source` (string, mixed)  
    Which column(s) or source values to show or use in the reference strategy.

- `strategy` (string)  
    The reference strategy to apply. This may be either an alias or class name.

- `search` (string, mixed)  
    The targets (columns, attributes) that will be used for standard means to find matches for the model 
    (in, f.i., a searchable ajax select dropdown).

The value formats for `source` and `search` may depend on the strategy used.

See [Model Reference Strategies](../Strategies.md#model-reference-strategies) for more information on the strategies that render the references.


## Single Mode

In some cases you may want to restrict an Eloquent model to have only one record.
In that case the listing may be disabled entirely, offering only the option to create one new model if none exists, or edit only the first (matching) model.

If required, this may be combined with repository context strategies to pick a specific existing model record.
  
To enable this 'single mode', set `single` to `true` in the model configuration.

```php
<?php
return [
    // ...
    
    'single' => true,
    
    // ...
];
```
