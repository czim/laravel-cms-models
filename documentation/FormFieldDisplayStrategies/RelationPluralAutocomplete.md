
# Form Field Display Strategy: RelationPluralAutocomplete

This strategy renders a select field with autocomplete functionality, 
that allows multiple models to be selected.

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
