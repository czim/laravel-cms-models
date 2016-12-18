<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelShowDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelShowDataInterface $with
     */
    public function merge(ModelShowDataInterface $with);

}
