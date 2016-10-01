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

    /**
     * Returns whether the model may be deleted at all.
     *
     * @return bool
     */
    public function allowDelete();

    /**
     * Returns delete condition if set, or false if not.
     *
     * @return string|string[]|false
     */
    public function deleteCondition();

    /**
     * Returns delete strategy if set, or false if not.
     *
     * @return string|false
     */
    public function deleteStrategy();

    /**
     * @param ModelInformationInterface $with
     */
    public function merge(ModelInformationInterface $with);

}
