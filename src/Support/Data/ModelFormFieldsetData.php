<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelFormFieldsetDataInterface;
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
class ModelFormFieldsetData extends AbstractModelFormLayoutNodeData  implements ModelFormFieldsetDataInterface
{

    protected $attributes = [

        'type' => LayoutNodeType::FIELDSET,

        // Fieldset label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Whether the fields belonging to this are required (affects display only)
        'required' => null,

        // Nested layout children (field keys or nested fieldsets/groups).
        'chilren' => [],
    ];

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
