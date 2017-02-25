<?php
namespace Czim\CmsModels\Support\Data;

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
 * @property bool $required
 * @property string $display_strategy
 * @property string $store_strategy
 * @property string $type
 * @property array $options
 * @property bool $translated
 * @property string $style
 * @property bool $admin_only
 * @property string|string[] $permissions
 * @property ModelViewReferenceData $before
 * @property ModelViewReferenceData $after
 */
class ModelFormFieldData extends AbstractModelInformationDataObject implements ModelFormFieldDataInterface
{

    protected $objects = [
        'before' => ModelViewReferenceData::class,
        'after'  => ModelViewReferenceData::class,
    ];

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

        // Whether the field must be filled in.
        'required' => null,

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

        // Custom options relevant for the strategy
        'options' => [],

        // Whether the field is visible & usable by super admins only
        'admin_only' => null,

        // A permission key or an array of permission keys that is required to see & use this field
        'permissions' => null,

        // Views to show before and/or after the form field. Instance of ModelViewReferenceData.
        'before' => null,
        'after'  => null,
    ];

    protected $known = [
        'key',
        'create',
        'update',
        'label',
        'label_translated',
        'source',
        'required',
        'display_strategy',
        'store_strategy',
        'type',
        'translated',
        'style',
        'options',
        'admin_only',
        'permissions',
        'before',
        'after',
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
     * Returns the source pattern for the form field.
     *
     * @return string
     */
    public function source()
    {
        if ($this->source) {
            return $this->source;
        }

        return $this->key;
    }

    /**
     * Returns whether the field must be filled in.
     *
     * @return bool
     */
    public function required()
    {
        if (null === $this->required) {
            return false;
        }

        return $this->required;
    }

    /**
     * Returns whether the field is translated.
     *
     * @return bool
     */
    public function translated()
    {
        if (null === $this->translated) {
            return false;
        }

        return $this->translated;
    }

    /**
     * Returns associative array with custom options for the strategy.
     *
     * @return array
     */
    public function options()
    {
        return $this->options ?: [];
    }

    /**
     * Returns whether only the super admin may use the field.
     *
     * @return bool
     */
    public function adminOnly()
    {
        return (bool) $this->admin_only;
    }

    /**
     * Returns permissions required to use the field.
     *
     * @return string[]
     */
    public function permissions()
    {
        if (is_array($this->permissions)) {
            return $this->permissions;
        }

        if ($this->permissions) {
            return [ $this->permissions ];
        }

        return [];
    }

    /**
     * @param ModelFormFieldDataInterface $with
     */
    public function merge(ModelFormFieldDataInterface $with)
    {
        $normalMerge = array_diff($this->getKeys(), ['options']);

        foreach ($normalMerge as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        $this->options = array_merge($this->options(), $with->options());
    }

}
