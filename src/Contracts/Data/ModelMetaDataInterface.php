<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelMetaDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelMetaDataInterface $with
     */
    public function merge(ModelMetaDataInterface $with);

}
