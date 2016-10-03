<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelFormDataInterface;
use Czim\CmsModels\Contracts\Data\ModelFormTabDataInterface;
use Czim\DataObject\Contracts\DataObjectInterface;
use UnexpectedValueException;

/**
 * Class ModelFormInformation
 *
 * Data container that represents form representation for the model.
 *
 * @property array                      $layout
 * @property array|ModelFormFieldData[] $fields
 */
class ModelFormData extends AbstractDataObject implements ModelFormDataInterface
{

    protected $objects = [
        'fields' => ModelFormFieldData::class . '[]',
    ];

    protected $attributes = [

        // The layout of the form fields
        // Tabs, Fieldsets and keys for fields (in the order they should appear).
        // Tabs and Fieldsets should be keyed by references to use for them.
        // If not set, simply shows fields in the order they are defined.
        'layout' => null,

        // Arrays (instances of ModelFormFieldData or ModelFormFieldGroupData) that define the editable fields for
        // the model's form in the order in which they should appear by default.
        'fields' => [],
    ];


    /**
     * Returns whether a layout with tabs is set.
     *
     * @return bool
     */
    public function hasTabs()
    {
        if ( ! $this->layout || ! count($this->layout)) {
            return false;
        }

        foreach ($this->layout as $key => $value) {

            if ($value instanceof ModelFormTabDataInterface) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns only the tabs from the layout set.
     *
     * @return array|ModelFormTabData[]
     */
    public function tabs()
    {
        if ( ! $this->layout || ! count($this->layout)) {
            return [];
        }

        $tabs = [];

        foreach ($this->layout as $key => $value) {

            if ($value instanceof ModelFormTabDataInterface) {
                $tabs[ $key ] = $value;
            }
        }

        return $tabs;
    }

    /**
     * Returns the layout that should be used for displaying the edit form.
     *
     * @return array|mixed[]
     */
    public function layout()
    {
        if ($this->layout && count($this->layout)) {
            return $this->layout;
        }

        return array_keys($this->fields);
    }

    /**
     * @param ModelFormDataInterface|ModelFormData $with
     */
    public function merge(ModelFormDataInterface $with)
    {
        // Overwrite fields intelligently: keep only the fields for keys that were set
        // and merge those for which data is set.
        if ($with->fields && count($with->fields)) {

            $mergedFields = [];

            foreach ($with->fields as $key => $data) {

                if (array_has($this->fields, $key)) {
                    $data = $this->fields[ $key ]->merge($data);
                }

                $mergedFields[ $key ] = $data;
            }

            $this->fields = $mergedFields;
        }

        $standardMergeKeys = [
            'layout',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

    /**
     * Converts attributes to specific dataobjects if configured to
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue($key)
    {
        if ($key !== 'layout') {
            return parent::getAttributeValue($key);
        }

        if ( ! isset($this->attributes[$key]) || ! is_array($this->attributes[$key])) {
            $null = null;
            return $null;
        }

        // If object is an array, interpret it by type and make the corresponding data object.
        $this->decorateLayoutAttribute($key);

        return $this->attributes[$key];
    }

    /**
     * @param string $topKey
     */
    protected function decorateLayoutAttribute($topKey = 'layout')
    {
        foreach ($this->attributes[$topKey] as $key => &$value) {

            if (is_array($value)) {

                $type = strtolower(array_get($value, 'type', ''));

                switch ($type) {

                    case 'tab':
                        $value = new ModelFormTabData($value);
                        break;

                    case 'fieldset':
                        $value = new ModelFormFieldsetData($value);
                        break;

                    case 'group':
                        $value = new ModelFormFieldGroupData($value);
                        break;

                    default:
                        throw new UnexpectedValueException("Unknown layout node type '{$type}'");
                }
            }
        }

        unset ($value);
    }

}
