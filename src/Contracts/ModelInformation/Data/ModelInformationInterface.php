<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data;

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
     * Returns label for single item.
     *
     * @param bool $translated  return translated if possible
     * @return string
     */
    public function label($translated = true);

    /**
     * Returns translation key for label for single item.
     *
     * @return string
     */
    public function labelTranslationKey();

    /**
     * Returns label for multiple items.
     *
     * @param bool $translated  return translated if possible
     * @return string
     */
    public function labelPlural($translated = true);

    /**
     * Returns translation key for label for multiple items.
     *
     * @return string
     */
    public function labelPluralTranslationKey();

    /**
     * Returns whether the model may be deleted at all.
     *
     * @return bool
     */
    public function allowDelete();

    /**
     * Returns whether deletions should be confirmed by the user.
     *
     * @return bool
     */
    public function confirmDelete();

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
