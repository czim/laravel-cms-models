<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelListColumnDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelListColumnDataInterface $with
     */
    public function merge(ModelListColumnDataInterface $with);

}
