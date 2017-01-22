# Strategies

Strategies keep the CMS flexible. The provided strategies will usually be adequate, but you can always write or extend your own.

Aliases in configuration files are added for convenience, so that a simple string resolves to a strategy class.


## Strategy References

For details and help on using strategies included in the CMS, check out the reference lists:

- [List Strategy reference list](ListStrategyReference.md) for strategies used in lists: columns, filters, actions. 
- [Form Strategy reference list](FormStrategyReference.md) for strategies used in forms: fields and storing values.


## Repository Context Strategies

Any model accessed in the CMS will have its records retrieved through a repository. 
The default query manipulations for the repository may be configured per model, as well as whether and which global scopes should be ignored by the CMS.

By default, all global scopes will be removed before results are retrieved.

No context strategies are provided with the CMS; roll your own if you need one.


## Model Reference Strategies

A model may be referred to, for instance when a model reference is shown in a list, or as an option in a select for linking it to other models. Each model 

Reference strategies take the referenced model and its (mixed) source, and render it as a string value.

Included:

TODO: none yet

### Fall-back values

If a model is not part of the CMS, its reference can (obviously) not be determined through defined model information.
In that case, it will fall back to using the model's primary key value.

If a `getReference()` or `getReferenceAttribute()` method is available on the model, this will be used instead.


## List Display Strategies

List strategies determine how data is rendered in list columns on a model's index page.
 
Included:

- **Check**: a graphic checkmark that reflects the source as a boolean state. 
- **RelationCount**: a number reflecting the amount of related records.
- **RelationReference**: a formatted string with one or more model references (see reference strategies above).
 

By default, if no strategy is defined, the source will be displayed as a plain string value.

### Source 

The display strategy renders HTML on the basis of a source value.



## Filter Strategies

Filter strategies determine how filter input fields are rendered at the top of the index list, and also how the filter is applied to the query builder.

### Standard Strategies

Included:

- **BasicString**: a text input that performs a loosy string search.
- **BasicSplitString**: same as BasicString, but the search terms are split by whitespace (so 'some term' will match `%some%` **or** `%term%`).
- **DropdownEnum**: a dropdown select input for picking a single enum value. 

### Targets

Each strategy takes a `target` parameter, which determines what datase columns are filtered against.

The target may be:

- a *string* with a model's attribute. This may be a direct attribute or a translated attribute.
- a dot-notation *string* that represents an attribute on a related model. 
This may be nested to any depth. (ex.: `author.name`, `post.comments.author.name`).
- a *string* value of `'*'`, which translates to all relevant attributes of the model.
- a *string* with any combination of the above, comma-separated. (ex.: `title,author.name`)
- an *array* with any of the above.

For translated attributes, the current and fallback locales are matched against.

When combining muliple targets, by default the **or** boolean combination operator is used.

## List Action Strategies

Action strategies determine a hyperlink used for a default action for clicking on a list row.  
Actions are defined in the model configuration under `list.default_action` and refer to a strategy of this kind.
 
Included:

- **Edit**: a link to the edit form for the model record. 
- **Show**: a link to the show page for the model record.
- **Children**: a link to a list of 'children' of the model record.

There is no default strategy, one of these (or a custom strategy) must be indicated.


## Form strategies

Form strategies determine how form fields are rendered on the edit page, and also how the form fields are stored for the model.

### Form display strategies

### Form store strategies

### Using references to other models

For some form display strategies, mainly those for managing relations, it may be necessary to allow the user to search for
and choose from intelligible references to other models.

When referencing models that are part of the CMS (ie. that have been configured to have their own model module), the target model's configuration is used. 
Its configured reference setup will be used to format the references returned to be rendered in the view (in a select dropdown or autocomplete field, for instance).

For models that are not part of the CMS, only the model keys will be shown as references. 
Use `strategy` and `source` values in the form field's `options` to configure references as required, as explained below.

In the `form.fields` section for the field for which a reference should configured, 
configure its `options` section for the following keys:
  
- `strategy` (string)  
    The reference strategy FQN, classname or alias to use,
    
- `source` (string)  
    The source attribute(s) to pass on to the reference strategy,
    
- `target` (string)  
    The target attributes to use for searching, if a search term for autocomplete strategies is given.  
    If no target is defined, the `source` attribute(s) are searched instead, if possible.
    
- `context_strategy` (string)  
    An extra optional context strategy FQN, classname or alias to modify the query for the references.

For an example of a form field display strategy that makes use of this, see `Czim\CmsModels\View\FormFieldStrategies\ModelRelationSingleStrategy`.

For more information on setting a `context_strategy`, see [Repository Context Strategies](STRATEGIES.md#repository-context-strategies).
     

This is a fairly simple and inflexible setup that is intended mainly for simple autocomplete input fields.
It uses the `Czim\CmsModels\Http\Controllers\ModelMetaController`'s `reference` action.

To prevent security issues, this expects as parameters the form field's parent model classname and an indication of where to
find the necessary reference setup in that model's configuration data (usually a field's key in the `form.fields` array).
This way, users can only access references configured explicitly in the CMS, and according to the permissions related to the
CMS model for which the references need to be accessed. 
It's advisable to keep this in mind when rolling your own reference setup.


### Target

The default target is a property on the model, but more elaborate handling of submitted form data may be handled by the strategy.


## Export strategies

Exporting can be initiated, if enabled, on a model's index listing page. These strategies determine what and how data is exported.

### Export strategies

Export strategies take a model listing query and render its results in an exported file, in formats such as CSV, XML, etc.

### Export column strategies

Export column strategies determine how data for each model attribute value is rendered in columns (or, say, XML properties) in a model listing export.
