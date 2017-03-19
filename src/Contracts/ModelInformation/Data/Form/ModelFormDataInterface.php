<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form;

use ArrayAccess;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Layout\ModelFormTabDataInterface;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFormDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns whether a layout with tabs is set.
     *
     * @return bool
     */
    public function hasTabs();

    /**
     * Returns only the tabs from the layout set.
     *
     * @return array|ModelFormTabDataInterface[]
     */
    public function tabs();

    /**
     * Returns the layout that should be used for displaying the edit form.
     *
     * @return array|mixed[]
     */
    public function layout();

    /**
     * Returns a list of form field keys present in the layout.
     *
     * @return string[]
     */
    public function getLayoutFormFieldKeys();

    /**
     * @param ModelFormDataInterface $with
     */
    public function merge(ModelFormDataInterface $with);

}
