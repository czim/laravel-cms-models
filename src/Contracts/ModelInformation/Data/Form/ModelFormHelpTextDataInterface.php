<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFormHelpTextDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns display help text.
     *
     * @return string
     */
    public function text();

    /**
     * Returns an identifier for an icon to display.
     *
     * @return string|null
     */
    public function icon();

    /**
     * Returns the style class to apply to the help text container.
     *
     * @return string|null
     */
    public function cssClass();

    /**
     * Returns the view partial to use for this help text.
     *
     * @return string|null
     */
    public function view();

    /**
     * Returns whether the text content should be HTML-escaped.
     *
     * @return bool
     */
    public function escape();

    /**
     * @param ModelFormHelpTextDataInterface $with
     */
    public function merge(ModelFormHelpTextDataInterface $with);

}
