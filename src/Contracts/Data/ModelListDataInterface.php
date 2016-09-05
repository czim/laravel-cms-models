<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelListDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelListDataInterface $with
     */
    public function merge(ModelListDataInterface $with);

}
