<?php
namespace Czim\CmsModels\Support\Data;

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
 * @property array  $options
 */
class ModelListFilterData extends AbstractModelInformationDataObject implements ModelFilterDataInterface
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

        // Custom options for the strategy
        'options' => [],
    ];

    protected $known = [
        'label',
        'label_translated',
        'source',
        'target',
        'strategy',
        'options',
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
     * Returns special options for the strategy.
     *
     * @return array
     */
    public function options()
    {
        return $this->options ?: [];
    }


    /**
     * @param ModelFilterDataInterface|ModelListFilterData $with
     */
    public function merge(ModelFilterDataInterface $with)
    {
        $standardMergeKeys = array_diff($this->getKeys(), ['options']);

        // Merge options separately
        $this->options = array_merge($this->options ?: [], $with->options ?: []);

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
