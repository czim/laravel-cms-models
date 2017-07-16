<?php
namespace Czim\CmsModels\ModelInformation\Data\Form\Layout;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Layout\ModelFormFieldsetDataInterface;
use Czim\CmsModels\Support\Enums\LayoutNodeType;

/**
 * Class ModelFormFieldsetData
 *
 * Data container that describes a fieldset in a model's create/update form
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property bool   $required
 * @property array  $children
 */
class ModelFormFieldsetData extends AbstractModelFormLayoutNodeData implements ModelFormFieldsetDataInterface
{

    protected $attributes = [

        'type' => LayoutNodeType::FIELDSET,

        // Fieldset label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Whether the fields belonging to this are required (affects display only)
        'required' => null,

        // Nested layout children (field keys or nested fieldsets/groups).
        'children' => [],
    ];

    protected $known = [
        'type',
        'label',
        'label_translated',
        'required',
        'children',
    ];


    /**
     * Returns whether the fieldset should be displayed.
     *
     * @return bool
     */
    public function shouldDisplay()
    {
        return (bool) count($this->children);
    }

    /**
     * @param ModelFormFieldsetDataInterface|ModelFormFieldsetData $with
     */
    public function merge(ModelFormFieldsetDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
