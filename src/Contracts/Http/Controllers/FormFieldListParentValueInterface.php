<?php
namespace Czim\CmsModels\Contracts\Http\Controllers;

interface FormFieldListParentValueInterface
{

    /**
     * Returns field value based on list parent key data.
     *
     * Only relevant for store strategies that may be used for fields that correspond to list parent relations.
     * May simply return null otherwise.
     *
     * @param string $key
     * @return mixed|null
     */
    public function valueForListParentKey($key);

}
