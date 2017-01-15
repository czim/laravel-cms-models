<?php
namespace Czim\CmsModels\Contracts\Support\Exporting;

use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\Data\ModelExportColumnDataInterface;
use Illuminate\Database\Eloquent\Model;

interface ExportColumnInterface
{

    /**
     * Renders a display value to print to the export.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string
     */
    public function render(Model $model, $source);

    /**
     * Returns custom options.
     *
     * @return array
     */
    public function options();

    /**
     * Sets custom options.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Sets the export column data to use.
     *
     * @param ModelExportColumnDataInterface $data
     * @return $this
     */
    public function setColumnInformation(ModelExportColumnDataInterface $data);

    /**
     * Sets the attribute column data to use.
     *
     * @param ModelAttributeDataInterface $data
     * @return $this
     */
    public function setAttributeInformation(ModelAttributeDataInterface $data);

    /**
     * Initializes the strategy instance for further calls.
     *
     * Should be called after setColumnInformation, if this is set at all.
     *
     * @param string $modelClass
     * @return $this
     */
    public function initialize($modelClass);

}
