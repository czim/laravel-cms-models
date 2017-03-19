<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Listing;

use ArrayAccess;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelActionReferenceDataInterface;
use Illuminate\Contracts\Support\Arrayable;

interface ModelListDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns the orderable (listify) column that should be used.
     *
     * @return string
     */
    public function getOrderableColumn();

    /**
     * Returns the default action for list rows.
     *
     * @return ModelActionReferenceDataInterface|null
     */
    public function getDefaultAction();

    /**
     * @param ModelListDataInterface $with
     */
    public function merge(ModelListDataInterface $with);

}
