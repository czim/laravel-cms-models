<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class AttachmentStaplerFileStrategy extends AbstractDefaultStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.attachment_stapler_file';
    }

}
