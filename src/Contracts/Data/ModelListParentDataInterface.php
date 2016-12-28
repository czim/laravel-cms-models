<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelListParentDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelListParentDataInterface $with
     */
    public function merge(ModelListParentDataInterface $with);

}
