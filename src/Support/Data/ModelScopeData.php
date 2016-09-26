<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelScopeDataInterface;

/**
 * Class ModelScopeData
 *
 * Information about a model's scope.
 *
 * @property string $method
 * @property string $label
 * @property string $label_translated
 * @property string $strategy
 */
class ModelScopeData extends AbstractDataObject implements ModelScopeDataInterface
{

    protected $attributes = [

        // Scope method name
        'method' => null,

        // Display label (or translated label)
        'label'            => null,
        'label_translated' => null,

        // General strategy for handling scope
        'strategy' => null,

    ];


    /**
     * Returns display text for the scope.
     *
     * @return string
     */
    public function display()
    {
        if ($this->label_translated) {
            return cms_trans($this->label_translated);
        }

        if ($this->label) {
            return $this->label;
        }

        return snake_case($this->method, ' ');
    }

    /**
     * @param ModelScopeDataInterface $with
     */
    public function merge(ModelScopeDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with[$key]);
        }
    }

}
