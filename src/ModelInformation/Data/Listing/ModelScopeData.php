<?php
namespace Czim\CmsModels\ModelInformation\Data\Listing;

use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelScopeDataInterface;
use Czim\CmsModels\ModelInformation\Data\AbstractModelInformationDataObject;

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
class ModelScopeData extends AbstractModelInformationDataObject implements ModelScopeDataInterface
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

    protected $known = [
        'method',
        'label',
        'label_translated',
        'strategy',
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
