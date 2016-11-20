# Form Field Store Strategy: Default

Default simple direct storage, used as a fallback when no strategy is specified.

Depending on the source, this retrieves data directly from the model's attributes, relation or any method with the same name as the source set.

This takes translated fields into account and retrieves and expects them nested in an associative array, keyed by the locale.
It is advisable for all other strategies to share this behavior.


