<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelFormFieldsetDataInterface;

/**
 * Class ModelFormFieldsetData
 *
 * Data container that describes a fieldset in a model's create/update form
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property array $children
 */
class ModelFormFieldsetData extends AbstractModelFormLayoutNodeData  implements ModelFormFieldsetDataInterface
{

    protected $attributes = [

        'type' => 'fieldset',

        // Fieldset label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

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
