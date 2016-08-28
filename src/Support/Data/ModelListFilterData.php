<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;

/**
 * Class ModelListFilterInformation
 *
 * Data container that represents a filter presence/strategy in an index/listing for a model.
 *
 * @property string $label
 * @property string $label_translated
 * @property string $source
 * @property string $target
 * @property string $strategy
 * @property array  $values
 */
class ModelListFilterData extends AbstractDataObject implements ModelFilterDataInterface
{

    protected $attributes = [

        // Column header or label (or translation key) to show
        'label' => null,
        'label_translated' => null,

        // If any known, the source that the filter is made for (attribute or relationship)
        'source' => null,

        // The target column, relation, or other strategy to filter against
        'target' => '',

        // The filter strategy to apply for rendering & application
        'strategy' => null,

        // Values for strategies that require a list of values
        'values' => [],

    ];

    /**
     * Returns friendly display label for the model.
     *
     * @return string
     */
    public function label()
    {
        if ($this->label_translated) {
            return cms_trans($this->label_translated);
        }

        return $this->label;
    }

    /**
     * Returns the source that the filter is made for (attribute or relationship).
     *
     * @return string
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * Returns target column, relation, or other strategy to filter against.
     *
     * @return string
     */
    public function target()
    {
        return $this->target;
    }

    /**
     * Returns the filter strategy to apply for rendering & application.
     *
     * @return string
     */
    public function strategy()
    {
        return $this->strategy;
    }

    /**
     * Return values for strategies that require a list of values.
     *
     * @return array
     */
    public function values()
    {
        return $this->values ?: [];
    }

    /**
     * @param ModelListFilterData $with
     */
    public function merge(ModelListFilterData $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }
}
