<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelFormLayoutNodeInterface;

/**
 * Class ModelFormFieldGroupData
 *
 * Data container for layout of an (in-row) group of editable fields on a model's edit form.
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property array|string[] $children
 */
class ModelFormFieldGroupData extends AbstractDataObject implements ModelFormLayoutNodeInterface
{
    protected $objects = [
    ];

    protected $attributes = [

        'type' => 'group',

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Nested layout (field keys only at this level)
        'children' => [],
    ];

    /**
     * Returns display label.
     *
     * @return string|null
     */
    public function display()
    {
        if ($this->label_translated) {
            return cms_trans($this->label_translated);
        }

        return $this->label;
    }

    /**
     * Returns the type of layout node.
     *
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Returns nested nodes or field keys.
     *
     * @return string[]|ModelFormLayoutNodeInterface[]
     */
    public function children()
    {
        return $this->children;
    }

}
