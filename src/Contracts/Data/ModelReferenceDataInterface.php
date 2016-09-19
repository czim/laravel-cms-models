<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelReferenceDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelReferenceDataInterface $with
     */
    public function merge(ModelReferenceDataInterface $with);

}
