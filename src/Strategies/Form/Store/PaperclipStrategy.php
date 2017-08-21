<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Czim\Paperclip\Attachment\Attachment;

class PaperclipStrategy extends StaplerStrategy
{

    /**
     * Returns the hash value that clears the attachment.
     *
     * @return string
     */
    protected function getNullAttachmentHash()
    {
        return Attachment::NULL_ATTACHMENT;
    }

}
