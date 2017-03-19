<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form;

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
     * Returns the source pattern for the form field.
     *
     * @return string
     */
    public function source();

    /**
     * Returns whether the field must be filled in.
     *
     * @return bool
     */
    public function required();

    /**
     * Returns whether the field is translated using the model's translation strategy.
     *
     * @return bool
     */
    public function translated();

    /**
     * Returns associative array with custom options for the strategy.
     *
     * @return array
     */
    public function options();

    /**
     * Returns whether only the super admin may use the field.
     *
     * @return bool
     */
    public function adminOnly();

    /**
     * Returns permissions required to use the field.
     *
     * @return string[]
     */
    public function permissions();

    /**
     * @param ModelFormFieldDataInterface $with
     */
    public function merge(ModelFormFieldDataInterface $with);

}
