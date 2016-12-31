## RelationCount

Gets the `count()` for the relation query and displays it.

The source should be a relationship method name. 


## RelationCountWithChildren

Gets the `count()` for the relation query and displays it as a link to the children model list.

This makes use of a [list parent setup](../ModelConfiguration/ListParentSetup.md) to function; be sure to configure this first. 

The source should be a relationship method name.


## RelationReference

This displays a reference for the model related through a singular relationship.  
The reference is drawn according to the configured [model reference strategy](../Strategies.md#model-reference-strategies) configuration.

Not usable for plural relationship methods.

The source should be a relationship method name.
