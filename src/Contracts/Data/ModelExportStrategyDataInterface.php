<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelExportStrategyDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns display label for the export link/button.
     *
     * @return string
     */
    public function label();

    /**
     * Returns options for the export strategy.
     *
     * @return array
     */
    public function options();

    /**
     * @param ModelExportStrategyDataInterface $with
     */
    public function merge(ModelExportStrategyDataInterface $with);

}
