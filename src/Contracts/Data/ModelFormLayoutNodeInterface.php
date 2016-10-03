<?php
namespace Czim\CmsModels\Contracts\Data;

use Czim\DataObject\Contracts\DataObjectInterface;

interface ModelFormLayoutNodeInterface extends DataObjectInterface
{

    /**
     * Returns the type of layout node.
     *
     * @return string
     */
    public function type();

}
