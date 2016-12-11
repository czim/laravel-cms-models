<?php
namespace Czim\CmsModels\Contracts\Data;

use Czim\DataObject\Contracts\DataObjectInterface;

interface ModelFormLayoutNodeInterface extends DataObjectInterface
{

    /**
     * Returns display label.
     *
     * @return string|null
     */
    public function display();

    /**
     * Returns the type of layout node.
     *
     * @return string
     */
    public function type();

    /**
     * Returns nested nodes or field keys.
     *
     * @return string[]|ModelFormLayoutNodeInterface[]
     */
    public function children();

    /**
     * Returns list of keys of form fields that are descendants of this tab.
     *
     * @return string[]
     */
    public function descendantFieldKeys();

    /**
     * Returns what field (key) the label for the node should connected with.
     *
     * @return string|null
     */
    public function labelFor();

}
