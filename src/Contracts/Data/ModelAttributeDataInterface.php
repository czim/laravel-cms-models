<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelAttributeDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @return bool
     */
    public function isNumeric();

    /**
     * @param ModelAttributeDataInterface $data
     */
    public function merge(ModelAttributeDataInterface $data);

    /**
     * Merges data for an attribute's translated column.
     *
     * @param ModelAttributeDataInterface $data
     */
    public function mergeTranslation(ModelAttributeDataInterface $data);

}
