<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class AttachmentStaplerImageStrategy extends AbstractDefaultStrategy
{
    const DEFAULT_ACCEPT = 'image/*';


    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.attachment_stapler_image';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        $data['accept'] = array_get($data['options'], 'accept', static::DEFAULT_ACCEPT);

        return $data;
    }

}
