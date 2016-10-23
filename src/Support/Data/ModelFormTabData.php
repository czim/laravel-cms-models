<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelFormTabDataInterface;

/**
 * Class ModelFormTabData
 *
 * Data container that describes a tab pane on a model's create/update form page
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property array $children
 */
class ModelFormTabData extends AbstractModelFormLayoutNodeData implements ModelFormTabDataInterface
{

    protected $attributes = [

        'type' => 'tab',

        // Tab label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Nested layout children (field keys or fieldsets/groups)
        'children' => [],
    ];

    /**
     * @param ModelFormTabDataInterface|ModelFormTabData $with
     */
    public function merge(ModelFormTabDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
