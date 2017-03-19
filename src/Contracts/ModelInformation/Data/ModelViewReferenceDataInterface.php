<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelViewReferenceDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns the view identifier.
     *
     * @return string|null
     */
    public function view();

    /**
     * Returns names for variables to be passed into the view.
     *
     * @return string[]
     */
    public function variables();

    /**
     * @param ModelViewReferenceDataInterface $with
     */
    public function merge(ModelViewReferenceDataInterface $with);

}
