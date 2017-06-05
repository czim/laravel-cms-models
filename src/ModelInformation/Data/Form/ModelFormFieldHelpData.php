<?php
namespace Czim\CmsModels\ModelInformation\Data\Form;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldHelpDataInterface;
use Czim\CmsModels\ModelInformation\Data\AbstractModelInformationDataObject;

/**
 * Class ModelFormFieldHelpData
 *
 * Container for information about model form field help texts to display.
 *
 * @property ModelFormHelpTextData $label
 * @property ModelFormHelpTextData $label_tooltip
 * @property ModelFormHelpTextData $field
 * @property ModelFormHelpTextData $field_tooltip
 */
class ModelFormFieldHelpData extends AbstractModelInformationDataObject implements ModelFormFieldHelpDataInterface
{
    protected $objects = [
        'label'         => ModelFormHelpTextData::class,
        'label_tooltip' => ModelFormHelpTextData::class,
        'field'         => ModelFormHelpTextData::class,
        'field_tooltip' => ModelFormHelpTextData::class,
    ];

    protected $attributes = [

        // Text to display in the field's label
        'label'         => [],
        // Text to display in the field's label as a tooltip
        'label_tooltip' => [],

        // Text to display in the field
        'field'         => [],
        // Text to display in the field as a tooltip
        'field_tooltip' => [],
    ];

    protected $known = [
        'label',
        'label_tooltip',
        'field',
        'field_tooltip',
    ];


    /**
     * @param ModelFormFieldHelpDataInterface|ModelFormFieldHelpData $with
     */
    public function merge(ModelFormFieldHelpDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
