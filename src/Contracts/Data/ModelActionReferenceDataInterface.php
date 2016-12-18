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
     * @param ModelActionReferenceDataInterface $with
     */
    public function merge(ModelActionReferenceDataInterface $with);

}
