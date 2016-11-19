# Model Configuration: General & Meta Data


## Meta section

The `meta` section stores overriding information for general handling of model-related requests.

To set a different controller for either web or API routes, set the FQN for the controller class in `meta.controller` and/or `meta.controller_api` respectively.

By default, global scopes are disabled when accessing models for the CMS. To prevent this, set `meta.disable_global_scopes` to `false`.

The repository can be set up by defining a strategy class reference (or alias) in `meta.repository_strategy`.
See [Repository Context Strategies](Strategies.md#repository-context-strategies) for more information on the strategies that set the repository's context.


## Model Reference

You can define how models will be referred to within the CMS, by setting the `reference` value (top level).

```
'reference' => [
    'source'   => 'title',
    'strategy' => \Czim\CmsModels\View\ReferenceStrategies\IdAndAttribute::class,
],
```

Keys that may be set:

- **source**: which column(s) or source values to show or use in the reference strategy.
- **strategy**: the reference strategy to apply.
- **search**: the targets (columns, attributes) that will be used for standard means to find matches for the model (in, f.i., a searchable ajax select dropdown).

See [Model Reference Strategies](Strategies.md#model-reference-strategies) for more information on the strategies that render the references.
