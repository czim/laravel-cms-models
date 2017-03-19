<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Export;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelExportColumnDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns display header label for the column.
     *
     * @return string
     */
    public function header();

    /**
     * Returns associative array with custom options for strategies.
     *
     * @return array
     */
    public function options();

    /**
     * @param ModelExportColumnDataInterface $with
     */
    public function merge(ModelExportColumnDataInterface $with);

}
