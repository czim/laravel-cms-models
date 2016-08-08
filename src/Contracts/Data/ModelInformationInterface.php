<?php
namespace Czim\CmsModels\Contracts\Data;

use Czim\DataObject\Contracts\DataObjectInterface;

interface ModelInformationInterface extends DataObjectInterface
{

    /**
     * Returns FQN of the Eloquent model.
     *
     * @return string
     */
    public function modelClass();

    /**
     * Returns friendly display label for the model.
     *
     * @return string
     */
    public function label();

    /**
     * Returns friendly display label for the plural model name.
     *
     * @return string
     */
    public function labelPlural();

}
