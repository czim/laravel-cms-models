<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Listing;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelScopeDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns display text for the scope.
     *
     * @return string
     */
    public function display();

    /**
     * @param ModelScopeDataInterface $data
     */
    public function merge(ModelScopeDataInterface $data);

}
