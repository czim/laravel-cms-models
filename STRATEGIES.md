# Strategies

Strategies keep the CMS flexible. The provided strategies will usually be adequate, but you can always write or extend your own.

Aliases in configuration files are added for convenience, so that a simple string resolves to a strategy class.


## List Display Strategies

List strategies determine how data is rendered in list columns on a model's index page.
 
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
- a dot-notation *string* that represents an attribute on a related model. This may be nested to any depth. (ex.: `author.name`, `post.comments.author.name`).
- a *string* value of `'*'`, which translates to all relevant attributes of the model.
- a *string* with any combination of the above, comma-separated. (ex.: `title,author.name`)
- an *array* with any of the above.

For translated attributes, the current and fallback locales are matched against.

When combining muliple targets, by default the **or** boolean combination operator is used.


## Form strategies

Form strategies determine how form fields are rendered on the edit page, and also how the form fields are stored for the model.

### Target

The default target is a property on the model, but more elaborate handling of submitted form data may be handled by the strategy.

