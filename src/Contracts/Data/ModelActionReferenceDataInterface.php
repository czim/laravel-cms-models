<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelActionReferenceDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns the special type identifier.
     *
     * @return string|null
     */
    public function type();

    /**
     * Returns the route name.
     *
     * @return string|null
     */
    public function route();

    /**
     * Returns names for variables to be passed into the view.
     *
     * @return string[]
     */
    public function variables();

    /**
     * Returns required permissions for this action.
     *
     * @return string[]
     */
    public function permissions();

    /**
     * Returns a query string segment to append to the link.
     *
     * @return mixed
     */
    public function query();

    /**
     * Returns custom options.
     *
     * @return array
     */
    public function options();

    /**
     * @param ModelActionReferenceDataInterface $with
     */
    public function merge(ModelActionReferenceDataInterface $with);

}
