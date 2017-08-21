<?php
namespace Czim\CmsModels\Strategies\Form\Display;

class AttachmentPaperclipFileStrategy extends AttachmentStaplerFileStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        if ($this->useFileUploader()) {
            return 'cms-models::model.partials.form.strategies.attachment_paperclip_file_uploader';
        }

        return 'cms-models::model.partials.form.strategies.attachment_paperclip_file';
    }

}
