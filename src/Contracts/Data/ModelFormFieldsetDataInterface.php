<?php
namespace Czim\CmsModels\Contracts\Data;

interface ModelFormFieldsetDataInterface extends ModelFormLayoutNodeInterface
{

    /**
     * Returns display label for the fieldset.
     *
     * @return string
     */
    public function display();

    /**
     * @param ModelFormFieldsetDataInterface $with
     */
    public function merge(ModelFormFieldsetDataInterface $with);

}
