<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsModels\Support\Strategies\Traits\UsesUploadModule;

class AttachmentStaplerImageStrategy extends AbstractDefaultStrategy
{
    use UsesUploadModule;

    const DEFAULT_ACCEPT = 'image/*';


    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        if ($this->useFileUploader()) {
            return 'cms-models::model.partials.form.strategies.attachment_stapler_image_uploader';
        }

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

        if ($this->useFileUploader()) {
            $data['uploadUrl']       = cms_route('fileupload.file.upload');
            $data['uploadDeleteUrl'] = cms_route('fileupload.file.delete', ['ID_PLACEHOLDER']);
        }

        return $data;
    }

    /**
     * Returns whether the file uploader model can and should be used.
     *
     * @return bool
     */
    protected function useFileUploader()
    {
        return ! array_get($this->field->options, 'no_ajax') && $this->isUploadModuleAvailable();
    }

}
