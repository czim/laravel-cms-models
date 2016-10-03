<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;

/**
 * Class ModelFormFieldData
 *
 * Data container that describes an editable field on a model's create/update form
 *
 * @property string $key
 * @property bool $create
 * @property bool $update
 * @property string $label
 * @property string $label_translated
 * @property string $source
 * @property string $type
 * @property string $presenter
 * @property string $style
 */
class ModelFormFieldData extends AbstractDataObject implements ModelFormFieldDataInterface
{

    protected $attributes = [

        // The unique identifying key for this field's value(s)
        // On submit, this key should hold the form's field value.
        'key' => null,

        // Whether the field should be present on a create form
        'create' => null,
        // Whether the field should be present on an update form
        'update' => null,

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Editing source/target to use for the form field. Similar to ModelListColumnData's source.
        'source' => null,

        // The strategy for rendering the form field (and present data for it).
        'display_strategy' => null,

        // The strategy for writing updates for the form field.
        'store_strategy' => null,

        // Form field general type ('text', 'password', 'checkbox', etc)
        // todo: remove this? replace with strategy-only approach?
        'type' => null,

        // Whether the value being edited is translated (follows the translation_strategy defined @ top level)
        'translated' => null,

        // Display style 'key' (css class, or whatever the front-end expects) that sets the rendering of the field
        'style' => null,

    ];

    /**
     * Returns the field key.
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Returns whether to show this field on the create form.
     *
     * @return bool
     */
    public function create()
    {
        if (null === $this->create) {
            return true;
        }

        return (bool) $this->create;
    }

    /**
     * Returns whether to show this field on the update form.
     *
     * @return bool
     */
    public function update()
    {
        if (null === $this->update) {
            return true;
        }

        return (bool) $this->update;
    }

    /**
     * Returns display label for form field.
     *
     * @return string
     */
    public function label()
    {
        if ($this->label_translated) {
            return cms_trans($this->label_translated);
        }

        if ($this->label) {
            return $this->label;
        }

        return ucfirst(str_replace('_', ' ', snake_case($this->key)));
    }

    /**
     * @param ModelFormFieldDataInterface $with
     */
    public function merge(ModelFormFieldDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
