<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelFormLayoutNodeInterface;
use Czim\CmsModels\Contracts\Data\ModelFormTabDataInterface;
use Czim\DataObject\Contracts\DataObjectInterface;
use UnexpectedValueException;

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
class ModelFormTabData extends AbstractDataObject implements
    ModelFormLayoutNodeInterface,
    ModelFormTabDataInterface
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
     * Returns display label for the tab lip.
     *
     * @return string
     */
    public function display()
    {
        if ($this->label_translated) {
            return cms_trans($this->label_translated);
        }

        return $this->label;
    }

    /**
     * @param ModelFormTabDataInterface|ModelFormTabData $with
     */
    public function merge(ModelFormTabDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
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
     * Converts attributes to specific dataobjects if configured to
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue($key)
    {
        if ($key !== 'children') {
            return parent::getAttributeValue($key);
        }

        if ( ! isset($this->attributes[$key]) || ! is_array($this->attributes[$key])) {
            $null = null;
            return $null;
        }

        // If object is an array, interpret it by type and make the corresponding data object.
        $this->decorateChildrenAttribute($key);

        return $this->attributes[$key];
    }

    /**
     * @param string $topKey
     */
    protected function decorateChildrenAttribute($topKey = 'children')
    {
        foreach ($this->attributes[$topKey] as $key => &$value) {

            if (is_array($value)) {

                $type = strtolower(array_get($value, 'type', ''));

                switch ($type) {

                    case 'fieldset':
                        $value = new ModelFormFieldsetData($value);
                        break;

                    case 'group':
                        $value = new ModelFormFieldGroupData($value);
                        break;

                    default:
                        throw new UnexpectedValueException("Unknown or unacceptable layout node type '{$type}'");
                }
            }
        }

        unset ($value);
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
