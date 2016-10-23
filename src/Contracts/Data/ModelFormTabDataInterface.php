<?php
namespace Czim\CmsModels\Contracts\Data;

interface ModelFormTabDataInterface extends ModelFormLayoutNodeInterface
{

    /**
     * Returns display label for the tab lip.
     *
     * @return string
     */
    public function display();

    /**
     * @param ModelFormTabDataInterface $with
     */
    public function merge(ModelFormTabDataInterface $with);

}
