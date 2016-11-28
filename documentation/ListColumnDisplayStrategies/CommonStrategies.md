## Default

This strategy is used if none has been configured for a list column.
Displays attribute content as-is.


## Check

Displays a simple green check mark or red cross, depending on whether the source content is true or false (cast as a boolean). 


## CheckNullable

Displays a simple green check mark or red cross, depending on whether the source content is true or false (cast as a boolean).  
If the value is null, neither is shown, leaving the cell empty.


## Date

Displays a `DateTime` value formatted to show only the date.  


## DateTime

Displays a `DateTime` value formatted to show date and time.


## Time

Displays a `DateTime` value formatted to show only the time.


## Stapler File

Displays a Stapler upload field as the filename, with a link to the uploaded file.


## Stapler Image

Displays a Stapler upload field for images as a thumbnail.
This uses the smallest available stapler resize by default. 
