# Form Field Display Strategy: Static
 
This strategy simply renders a static display of a value. 
Users cannot interact with this for editing.

It does not submit any value (there is no input field).


## Options

- `strategy` (string)  
    An FQN, class name or alias for a list display strategy.  
    This offers the option to use any list column display approach for static fields.

- `strategy_source` (string)  
    An overriding source string to use for the `strategy`.  
    If not set, uses the source (or key) of the form field display strategy itself.

- `strategy_options` (array)  
    Options specific for the `strategy`.
    

See the [list strategy reference](../ListStrategyReference.md) for strategies that may be used for the `strategy` option.
 
 
 ```php
     'options' => [
        'strategy'         => 'date',
        'strategy_source'  => 'attribute_name',
        'strategy_options' => [
            'format' => 'd/m/Y',
        ],

     ]
 ```

