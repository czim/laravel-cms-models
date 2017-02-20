<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelShowFieldDataInterface;

/**
 * Class ModelShowFieldData
 *
 * Data container that describes a displayed field on a model's show page
 *
 * @property string $label
 * @property string $label_translated
 * @property string $source
 * @property string $strategy
 * @property array $options
 * @property bool $translated
 * @property bool $admin_only
 * @property string|string[] $permissions
 */
class ModelShowFieldData extends AbstractModelInformationDataObject implements ModelShowFieldDataInterface
{

    protected $attributes = [

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Editing source/target to use for the show field. Similar to ModelListColumnData's source.
        'source' => null,

        // The strategy for rendering the show field (and present data for it).
        'strategy' => null,

        // Whether the value being shown is translated (follows the translation_strategy defined @ top level)
        'translated' => null,

        // Custom options relevant for the strategy
        'options' => [],

        // Whether the field is visible & usable by super admins only
        'admin_only' => null,

        // A permission key or an array of permission keys that is required to see & use this field
        'permissions' => null,
    ];

    protected $known = [
        'label',
        'label_translated',
        'source',
        'strategy',
        'translated',
        'options',
        'admin_only',
        'permissions',
        'style',
    ];


    /**
     * Returns display label for show field.
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
     * Returns the source pattern for the show field.
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
     * Returns whether only the super admin may see the field.
     *
     * @return bool
     */
    public function adminOnly()
    {
        return (bool) $this->admin_only;
    }

    /**
     * Returns permissions required to see the field.
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
     * @param ModelShowFieldDataInterface $with
     */
    public function merge(ModelShowFieldDataInterface $with)
    {
        $normalMerge = array_diff($this->getKeys(), ['options']);

        foreach ($normalMerge as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        $this->options = array_merge($this->options(), $with->options());
    }

}
