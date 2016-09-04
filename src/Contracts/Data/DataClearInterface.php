<?php
namespace Czim\CmsModels\Contracts\Data;

interface DataClearInterface
{

    /**
     * Clears the attributes.
     *
     * Note that this does not reset defaults, but clears them.
     *
     * @return $this
     */
    public function clear();

}
