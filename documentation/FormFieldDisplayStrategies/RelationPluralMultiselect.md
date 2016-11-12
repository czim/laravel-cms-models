
# Form Field Display Strategy: RelationPluralMultiselect

This strategy renders a select field with all the available models that may be related as options.

Note that this is only usable for sets of models that are guaranteed to be small.

It submits an array with model keys that may be stored with the [RelationPluralKey store strategy](../FormFieldStoreStrategies/RelationPluralKey.md).

## Options

It is generally not necessary to set options for this strategy if the related model is part of the CMS.
In any case these options may still be overridden to specificy specific reference behavior.
 
 ```php
     'options' => [
     
         // The source column(s) to use for the reference strategy 
         'source' => 'title',
         
         // The target column(s) to search when using autocomplete search strings
         'target' => 'title,name,id',
     ]
 ```
 
 ```php
     'options' => [
     
         // The source column(s) to use for the reference strategy 
         'source' => 'title',
         
         // The target column(s) to search when using autocomplete search strings
         'target' => 'title,name,id',
     ]
 ```
