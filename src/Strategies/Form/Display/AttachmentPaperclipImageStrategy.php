<?php
namespace Czim\CmsModels\Strategies\Form\Display;

class AttachmentPaperclipImageStrategy extends AttachmentStaplerImageStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        if ($this->useFileUploader()) {
            return 'cms-models::model.partials.form.strategies.attachment_paperclip_image_uploader';
        }

        return 'cms-models::model.partials.form.strategies.attachment_paperclip_image';
    }

}
