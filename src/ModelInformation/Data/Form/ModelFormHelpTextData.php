<?php
namespace Czim\CmsModels\ModelInformation\Data\Form;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormHelpTextDataInterface;
use Czim\CmsModels\ModelInformation\Data\AbstractModelInformationDataObject;

/**
 * Class ModelFormHelpTextData
 *
 * Container for information about model form field help text.
 *
 * @property string $text
 * @property string $text_translated
 * @property string $icon
 * @property string $class
 * @property string $view
 */
class ModelFormHelpTextData extends AbstractModelInformationDataObject implements ModelFormHelpTextDataInterface
{

    protected $attributes = [

        // The help text to display, or to display translated
        'text'            => null,
        'text_translated' => null,

        // Icon identifier
        'icon' => null,

        // Style class to apply to the text's container
        'class' => null,

        // View partial to use, to which text content is passed as $text
        'view' => null,
    ];

    protected $known = [
        'text',
        'text_translated',
        'icon',
        'class',
        'view',
    ];


    /**
     * Returns display help text.
     *
     * @return string
     */
    public function text()
    {
        if ($this->text_translated) {
            return cms_trans($this->text_translated);
        }

        return $this->text;
    }

    /**
     * Returns an identifier for an icon to display.
     *
     * @return string|null
     */
    public function icon()
    {
        return $this->icon;
    }

    /**
     * Returns the style class to apply to the help text container.
     *
     * @return string|null
     */
    public function class()
    {
        return $this->class;
    }

    /**
     * Returns the view partial to use for this help text.
     *
     * @return string|null
     */
    public function view()
    {
        return $this->view;
    }

    /**
     * @param ModelFormHelpTextDataInterface|ModelFormHelpTextData $with
     */
    public function merge(ModelFormHelpTextDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
