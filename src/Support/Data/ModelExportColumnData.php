<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelExportColumnDataInterface;

/**
 * Class ModelExportColumnInformation
 *
 * Data container that represents a column presence in an export for a model.
 *
 * @property bool $hide
 * @property string $source
 * @property string|array $strategy
 * @property string $header
 * @property string $header_translated
 * @property array $options
 */
class ModelExportColumnData extends AbstractModelInformationDataObject implements ModelExportColumnDataInterface
{

    protected $attributes = [

        // Whether to hide the export column.
        'hide' => false,

        // The source column or strategy to use. This may be a column on the model, or on models related to it.
        'source' => null,

        // Display strategy <FQN> or alias for displaying the source in the list.
        'strategy' => null,

        // Column header (or translation key) to show.
        'header' => null,
        'header_translated' => null,

        // Extra options for strategy configuration
        'options' => [],
    ];

    /**
     * Returns display header label for the column.
     *
     * @return string
     */
    public function header()
    {
        if ($this->header_translated) {
            return cms_trans($this->header_translated);
        }

        if ($this->header) {
            return $this->header;
        }

        return ucfirst(str_replace('_', ' ', snake_case($this->source)));
    }

    /**
     * Returns associative array with custom options for strategies.
     *
     * @return array
     */
    public function options()
    {
        return $this->options ?: [];
    }

    /**
     * @param ModelExportColumnDataInterface|ModelExportColumnData $with
     */
    public function merge(ModelExportColumnDataInterface $with)
    {
        $normalMerge = array_diff($this->getKeys(), ['options']);

        foreach ($normalMerge as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        $this->options = array_merge($this->options(), $with->options());
    }

}
