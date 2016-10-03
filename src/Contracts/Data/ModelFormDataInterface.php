<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFormDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelFormDataInterface $with
     */
    public function merge(ModelFormDataInterface $with);

}
