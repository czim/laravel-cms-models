<?php
namespace Czim\CmsModels\ModelConfig;

/**
 * Class Field
 *
 * This object does not correspond directly to the model information data structure.
 * Instead, it forms a Nova-like approach to defining fields, which is translated
 * back into the model information array structure.
 */
class Field
{

    /**
     * @var array 
     */
    protected $listColumn = [];

    /**
     * @var array
     */
    protected $formField = [];
    
    
    public function __construct(string $name = null, string $attribute = null)
    {
        $this->listColumn['label']  = $name;
        $this->listColumn['source'] = $attribute;
    }


    public static function make(): Field
    {
        return new static;
    }

    /**
     * @param string $label
     * @return Field|$this
     */
    public function literalLabel(string $label): Field
    {
        $this->listColumn['label'] = $label;

        return $this;
    }


    public function toListFieldArray(): array
    {
        // todo
    }

    public function toFormFieldArray(): array
    {
        // todo
    }

    public function toExportFieldArray(): array
    {
        // todo
    }

    public function toValidationRulesArray(): array
    {
        // todo
    }
    

    // Nova compatibility

    // make(name, attribute)

    // hideFromIndex
    // hideFromDetail
    // hideWhenCreating
    // hideWhenUpdating
    // onlyOnIndex
    // onlyOnDetail
    // onlyOnForms
    // exceptOnForms

    // sortable

    // Panel with (title, list of fields)    = fieldset
    // + Tab (same)

}
