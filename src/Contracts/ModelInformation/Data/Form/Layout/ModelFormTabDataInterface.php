<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form\Layout;

interface ModelFormTabDataInterface extends ModelFormLayoutNodeInterface
{

    /**
     * Returns display label for the tab lip.
     *
     * @return string
     */
    public function display();

    /**
     * Returns whether the tab-pane should be displayed
     *
     * @return bool
     */
    public function shouldDisplay();

    /**
     * @param ModelFormTabDataInterface $with
     */
    public function merge(ModelFormTabDataInterface $with);

}
