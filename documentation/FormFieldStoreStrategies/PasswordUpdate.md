# Form Field Store Strategy: Password Update

This only writes to the model if a value was submitted.

The value is passed through `Hash::make()` before it is stored.

The intended use for this is when a password *may* be updated, but an existing password should not be nullified otherwise.
