<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFormFieldDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns the field key.
     *
     * @return string
     */
    public function key();

    /**
     * Returns whether to show this field on the create form.
     *
     * @return bool
     */
    public function create();

    /**
     * Returns whether to show this field on the update form.
     *
     * @return bool
     */
    public function update();

    /**
     * Returns display label for form field.
     *
     * @return string
     */
    public function label();

    /**
     * @param ModelFormFieldDataInterface $with
     */
    public function merge(ModelFormFieldDataInterface $with);

}
