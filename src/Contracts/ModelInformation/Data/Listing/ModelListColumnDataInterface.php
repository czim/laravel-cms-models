<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Listing;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelListColumnDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns display header label for the column.
     *
     * @return string
     */
    public function header();

    /**
     * @param ModelListColumnDataInterface $with
     */
    public function merge(ModelListColumnDataInterface $with);

}
