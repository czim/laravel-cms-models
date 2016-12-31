# Model Configuration: List Parent Setup

Model relations that reflect typical 'parent-child' relations can be set up to provide click-through functionality.
Typical examples for when this is useful:

- In webshops, *categories* for *products*.
- Nested *categories*, where one *category* may be the parent of further, deeper nested *categories*. 
- Nested content *pages*, where one *page* may be the parent of deeper nested *pages*.

In practice, this corresponds to scenario's as follows:
 
- You open up the list of categories. Only top level categories are shown.  
    Clicking on a category opens a list of its children categories.  
    You can keep clicking on categories to access deeper nested children.  
- You open a list of categories.  
    Clicking on a category opens a list of the products in that category

These scenario's may also be combined (with the right setup):

- You open up the list of categories. Only top level categories are shown.  
    Clicking on a 'children' link for categories opens up a list of its children categories (recursively).  
    Clicking on a 'children' link for products opens up a list of products for that category (at any depth).

In any case, breadcrumbs at the top of the page will reflect the nesting level.

This offers a powerful CMS experience, at the cost of having to perform a bit more configuration.

For this to work, both parent and child models must be configured as part of the CMS and have their own model configuration.


## Defining List Parents for Children

Models that may be a 'child' for another model (or itself) require setting 
`list.parents` and, optionally, `list.default_top_relation` in the model configuration.

Consider a `Category` model that has a `parent_id` foreign key column that references itself.
It has a `childCategories()` Eloquent relation method that returns a `hasMany` to other category models,
and a `parentCategory()` Eloquent relation method that returns a `belongsTo` to another category model.


To configure the `Category` model to show only top-level categories (that have `null` values for `parent_id`),
and that will allow setting up a click-through action to show nested children, would require the following: 


```php
<?php
    'list' => [
         
        // One ore more Eloquent relation method names that point to a 'parent' for this 'child' model  
        'parents' => [
            'parentCategory',
        ],
     
        // If this is set to one of the relations under 'parents', 
        'default_top_relation' => 'parentCategory'
    ]
```

Any model that has the above set up (at least the relation in the `parents` list) may be targeted using an action on any model.
In this example, it would be an action on `Category` itself, but it could be any model that the relations in question refer to.



## Set up the Click-through to Children Action 

There are two options for offering the possibility to click through to the children list for a record:

- A [list column](List.md#column-display) with a hyperlink to the children listing
- A [default action](List.md#default-actions) that makes the entire list row a link to the children listing


### List Column Strategy

To set up a list column for this, fill the `list.columns` array with the columns you wish to include.
Note that if the columns are not set, all the columns you want to show up must be included.

Add an entry for the [RelationCountWithChildren](../ListColumnDisplayStrategies/RelationStrategies.md#relationcountwithchildren) list display strategy:

```php
<?php
    'list' => [
        'columns' => [
            
            // ...
            
            'childCategories' => [
                
                // The alias for the strategy that points to the RelationCountWithChildrenStrategy class.
                'strategy' => 'relation-count-children-link',
                
                'options'  => [
                    // The relation must be set in the options for this to work.
                    // This is the relation method name defined for the target model (Category in this case)
                    // that refers back to this parent model (ie. the reverse of the childCategories relation, here)
                    'relation' => 'parentCategory',
                ]
            ],
            
            // ...
        ],    
    ]
```

See [more information about setting up list columns](List.md#column-display) in general.


### Default Action Approach

To set up a default action, define it in the model configuration for the parent model, under `list.default_action`.

Add the following entry to it:

```php
<?php
    'list' => [
        
        
        'default_action' => [
            
            // ...
            
            [
                // A strategy alias for the ChildrenStrategy.
                'strategy' => 'children',
                
                // The permission key required for seeing the list of children. 
                'permissions' => 'models.app-models-category.show',
                
                'options' => [
                    // The target child model must be set here (ie. the model that has the relation method set below. 
                    'model'    => \App\Models\Category::class,
                    // The relation method that refers back to this, the parent, model.
                    'relation' => 'parentCategory',
                ],
            ],
            
            // ...
        ],    
    ]
```

Note that the options `model` and `relation` *must* be set for this to work.

See [more information on setting up default actions](List.md#default-actions) in general.
