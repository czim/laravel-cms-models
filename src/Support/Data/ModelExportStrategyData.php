<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelExportStrategyDataInterface;

/**
 * Class ModelExportStrategyData
 *
 * Data container that represents list representation for the model.
 *
 * @property string $strategy
 * @property string $label
 * @property string $label_translated
 * @property string|string[] $permissions
 * @property string $repository_strategy
 * @property array $repository_strategy_parameters
 * @property array|ModelExportColumnData[] $columns
 * @property array $options
 */
class ModelExportStrategyData extends AbstractDataObject implements ModelExportStrategyDataInterface
{

    protected $objects = [
        'columns' => ModelExportColumnData::class . '[]',
    ];

    protected $attributes = [

        // The strategy identifier (alias or FQN) for the exporting strategy.
        'strategy' => null,

        // Label (or translation key) to show on the export action link/button.
        'label' => null,
        'label_translated' => null,

        // The permission(s) required to use this export strategy (string or array of strings).
        'permissions' => null,

        // The strategy to apply to the base repository/context query for this export.
        'repository_strategy' => null,

        // Optional parameters to pass along to the repository/context strategy instance.
        'repository_strategy_parameters' => [],

        // Arrays (instances of ModelExportColumnData) with information about a single column.
        // All columns that should be present in the export, should be listed here, in the right order.
        // Overrules default export columns, if set.
        'columns' => [],

        // Options for this export strategy.
        'options' => [],
    ];

    /**
     * Returns display label for the export link/button.
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

        return ucfirst(str_replace('_', ' ', snake_case($this->strategy)));
    }

    /**
     * Returns permissions required to use the export strategy.
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
     * Returns options for the export strategy.
     *
     * @return array
     */
    public function options()
    {
        return $this->options ?: [];
    }

    /**
     * @param ModelExportStrategyDataInterface|ModelExportStrategyData $with
     * @return $this
     */
    public function merge(ModelExportStrategyDataInterface $with)
    {
        // Overwrite columns intelligently: keep only the columns for keys that were set
        // and merge those for which data is set.
        if ($with->columns && count($with->columns)) {

            $mergedColumns = [];

            foreach ($with->columns as $key => $data) {

                if (array_has($this->columns, $key)) {
                    $data = $this->columns[ $key ]->merge($data);
                }

                $mergedColumns[ $key ] = $data;
            }

            $this->columns = $mergedColumns;
        }

        $standardMergeKeys = [
            'permissions',
            'repository_strategy',
            'repository_strategy_parameters',
            'strategies',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        $this->options = array_merge($this->options(), $with->options());

        return $this;
    }

}
