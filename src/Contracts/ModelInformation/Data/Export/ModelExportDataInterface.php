<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Export;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelExportDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelExportDataInterface $with
     */
    public function merge(ModelExportDataInterface $with);

}
