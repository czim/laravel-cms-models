<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelListParentDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns the relation method name.
     *
     * @return mixed
     */
    public function relation();

    /**
     * Returns the field key.
     *
     * @return mixed
     */
    public function field();

    /**
     * @param ModelListParentDataInterface $with
     */
    public function merge(ModelListParentDataInterface $with);

}
