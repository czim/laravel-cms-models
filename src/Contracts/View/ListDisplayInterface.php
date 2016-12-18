<?php
namespace Czim\CmsModels\Contracts\View;

use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\Data\ModelListColumnDataInterface;
use Illuminate\Database\Eloquent\Model;

interface ListDisplayInterface extends ShowFieldInterface
{

    /**
     * Sets the list column data to use.
     *
     * @param ModelListColumnDataInterface $data
     * @return $this
     */
    public function setListInformation(ModelListColumnDataInterface $data);

}
