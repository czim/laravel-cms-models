<?php
namespace Czim\CmsModels\Contracts\Strategies;

use Czim\CmsModels\Contracts\Data\ModelListColumnDataInterface;

interface ListDisplayInterface extends ShowFieldInterface
{

    /**
     * Initializes the strategy instance for further calls.
     *
     * Should be called after setListInformation, if this is set at all.
     *
     * @param string $modelClass
     * @return $this
     */
    public function initialize($modelClass);

    /**
     * Sets the list column data to use.
     *
     * @param ModelListColumnDataInterface $data
     * @return $this
     */
    public function setListInformation(ModelListColumnDataInterface $data);

}
