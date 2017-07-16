<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form\Layout;

interface ModelFormFieldsetDataInterface extends ModelFormLayoutNodeInterface
{

    /**
     * Returns display label for the legend.
     *
     * @return string
     */
    public function display();

    /**
     * Returns whether the fieldset should be displayed.
     *
     * @return bool
     */
    public function shouldDisplay();

    /**
     * @param ModelFormFieldsetDataInterface $with
     */
    public function merge(ModelFormFieldsetDataInterface $with);

}
