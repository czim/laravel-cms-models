<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFormTabDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns display label for the tab lip.
     *
     * @return string
     */
    public function display();

    /**
     * @param ModelFormTabDataInterface $with
     */
    public function merge(ModelFormTabDataInterface $with);

}
