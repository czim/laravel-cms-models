<?php
namespace Czim\CmsModels\Contracts\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelActionReferenceDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns the strategy class or alias.
     *
     * @return string
     */
    public function strategy();

    /**
     * Returns required permissions for this action.
     *
     * @return string[]
     */
    public function permissions();

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
