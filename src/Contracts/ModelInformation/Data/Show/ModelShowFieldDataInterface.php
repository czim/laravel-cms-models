<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Show;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelShowFieldDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns display label for show field.
     *
     * @return string
     */
    public function label();

    /**
     * Returns the source pattern for the show field.
     *
     * @return string
     */
    public function source();

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
     * Returns whether only the super admin may see the field.
     *
     * @return bool
     */
    public function adminOnly();

    /**
     * Returns permissions required to see the field.
     *
     * @return string[]
     */
    public function permissions();

    /**
     * @param ModelShowFieldDataInterface $with
     */
    public function merge(ModelShowFieldDataInterface $with);

}
