<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelExportDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelExportDataInterface $with
     */
    public function merge(ModelExportDataInterface $with);

}
